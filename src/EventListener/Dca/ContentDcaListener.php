<?php

declare(strict_types=1);

namespace Hofff\Contao\RateIt\EventListener\Dca;

use Contao\DataContainer;
use Hofff\Contao\RateIt\DcaHelper;

final class ContentDcaListener extends DcaHelper
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(DataContainer $dc)
    {
        if ($dc->activeRecord->type == "gallery") {
            $type = 'galpic';

            // Alle vorherigen Bilder erst mal auf inaktiv setzen
            $this->Database->prepare("UPDATE tl_rateit_items SET active='' WHERE rkey LIKE ? AND typ=?")->execute($dc->activeRecord->id . '|%', $type);

            if (version_compare(VERSION, '3.2', '>=')) {
                $objFiles = \FilesModel::findMultipleByUuids(deserialize($dc->activeRecord->multiSRC));
            } else {
                $objFiles = \FilesModel::findMultipleByIds(deserialize($dc->activeRecord->multiSRC));
            }

            if ($objFiles !== null) {
                // Get all images
                while ($objFiles->next()) {
                    // Single files
                    if ($objFiles->type == 'file') {
                        $objFile = new \File($objFiles->path, true);

                        if (! $objFile->isGdImage) {
                            continue;
                        }

                        $this->insertOrUpdateRatingItemGallery($dc, $type, $objFile->name, $objFiles->id, ($dc->activeRecord->rateit_active ? '1' : ''));
                    } // Folders
                    else {
                        if (version_compare(VERSION, '3.2', '>=')) {
                            $objSubfiles = \FilesModel::findByPid($objFiles->uuid);
                        } else {
                            $objSubfiles = \FilesModel::findByPid($objFiles->id);
                        }

                        if ($objSubfiles === null) {
                            continue;
                        }

                        while ($objSubfiles->next()) {
                            // Skip subfolders
                            if ($objSubfiles->type == 'folder') {
                                continue;
                            }

                            $objFile = new \File($objSubfiles->path, true);

                            if (! $objFile->isGdImage) {
                                continue;
                            }

                            $this->insertOrUpdateRatingItemGallery($dc, $type, $objSubfiles->name, $objSubfiles->id, ($dc->activeRecord->rateit_active ? '1' : ''));
                        }
                    }
                }
            }
            return true;
        } else {
            return $this->insertOrUpdateRatingKey($dc, 'ce', $dc->activeRecord->rateit_title);
        }
    }

    public function delete(DataContainer $dc)
    {
        if ($dc->activeRecord->type == "gallery") {
            $this->Database->prepare("DELETE FROM tl_rateit_ratings WHERE pid IN (SELECT `id` FROM tl_rateit_items WHERE rkey LIKE ? AND typ=?)")
                ->execute($dc->activeRecord->id . '|%', 'galpic');
            $this->Database->prepare("DELETE FROM tl_rateit_items WHERE rkey LIKE ? AND typ=?")->execute($dc->activeRecord->id . '|%', 'galpic');
            return true;
        } else {
            return $this->deleteRatingKey($dc, 'ce');
        }
    }

    private function insertOrUpdateRatingItemGallery(DataContainer $dc, $type, $strName, $imgId, $active)
    {
        $rkey     = $dc->activeRecord->id . '|' . $imgId;
        $headline = deserialize($dc->activeRecord->headline);
        $title    = $dc->activeRecord->id;
        if (is_array($headline) && array_key_exists('value', $headline) && strlen($headline['value']) > 0) {
            $title = $headline['value'];
        }
        $ratingTitle = $title . ' - ' . $strName;
        $actRecord   = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ=?")
            ->execute($rkey, $type)
            ->fetchAssoc();
        if (! is_array($actRecord)) {
            $arrSet       = array('rkey'      => $rkey,
                                  'tstamp'    => time(),
                                  'typ'       => $type,
                                  'createdat' => time(),
                                  'title'     => $ratingTitle,
                                  'active'    => $active,
            );
            $insertRecord = $this->Database->prepare("INSERT INTO tl_rateit_items %s")
                ->set($arrSet)
                ->execute()
                ->insertId;
        } else {
            $this->Database->prepare("UPDATE tl_rateit_items SET active=?, title=? WHERE rkey=? and typ=?")
                ->execute($active, $ratingTitle, $rkey, $type)
                ->updatedId;
        }
    }
}
