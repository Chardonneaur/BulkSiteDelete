<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkSiteDelete;

use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;

class API extends \Piwik\Plugin\API
{
    /**
     * Delete multiple sites at once.
     *
     * @param string $idSites Comma-separated list of site IDs to delete
     * @return array{deletedCount: int}
     * @throws \Exception If no valid site IDs provided or deletion fails
     */
    public function deleteMultipleSites(string $idSites): array
    {
        Piwik::checkUserHasSuperUserAccess();

        $siteIds = array_filter(array_map('intval', explode(',', $idSites)));

        if (empty($siteIds)) {
            throw new \Exception(Piwik::translate('BulkSiteDelete_NoSitesSelected'));
        }

        $sitesManagerApi = SitesManagerAPI::getInstance();
        $deletedCount = 0;
        $errors = [];

        foreach ($siteIds as $idSite) {
            try {
                $sitesManagerApi->deleteSite($idSite);
                $deletedCount++;
            } catch (\Exception $e) {
                $errors[] = "Site $idSite: " . $e->getMessage();
            }
        }

        if (!empty($errors) && $deletedCount === 0) {
            throw new \Exception(implode('; ', $errors));
        }

        return [
            'deletedCount' => $deletedCount,
        ];
    }
}
