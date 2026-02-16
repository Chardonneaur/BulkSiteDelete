<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkSiteDelete;

use Piwik\Common;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\View;

class Controller extends \Piwik\Plugin\ControllerAdmin
{
    const DELETE_NONCE = 'BulkSiteDelete.delete';

    public function index(): string
    {
        Piwik::checkUserHasSuperUserAccess();

        // Handle POST delete action
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleDelete();
        }

        $view = new View('@BulkSiteDelete/index');
        $this->setGeneralVariablesView($view);

        // Load sites server-side
        $allSites = SitesManagerAPI::getInstance()->getAllSites();
        $sites = [];
        foreach ($allSites as $site) {
            $sites[] = [
                'idsite'   => (int) $site['idsite'],
                'name'     => $site['name'],
                'main_url' => $site['main_url'],
            ];
        }
        usort($sites, function ($a, $b) {
            return $a['idsite'] - $b['idsite'];
        });

        $view->sites = $sites;
        $view->sitesJson = json_encode($sites);
        $view->deleteNonce = Nonce::getNonce(self::DELETE_NONCE);

        return $view->render();
    }

    private function handleDelete(): void
    {
        $nonce = Common::getRequestVar('nonce', '', 'string', $_POST);
        Nonce::checkNonce(self::DELETE_NONCE, $nonce);

        $idSitesRaw = Common::getRequestVar('idSites', '', 'string', $_POST);
        $siteIds = array_filter(array_map('intval', explode(',', $idSitesRaw)));

        if (empty($siteIds)) {
            $notification = new Notification(Piwik::translate('BulkSiteDelete_NoSitesSelected'));
            $notification->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify('BulkSiteDelete_Error', $notification);
            return;
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

        if ($deletedCount > 0) {
            $notification = new Notification(
                Piwik::translate('BulkSiteDelete_DeletedSuccess', $deletedCount)
            );
            $notification->context = Notification::CONTEXT_SUCCESS;
            Notification\Manager::notify('BulkSiteDelete_Success', $notification);
        }

        if (!empty($errors)) {
            $notification = new Notification(implode('; ', $errors));
            $notification->context = Notification::CONTEXT_ERROR;
            Notification\Manager::notify('BulkSiteDelete_Error', $notification);
        }
    }
}
