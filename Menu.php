<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\BulkSiteDelete;

use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;

class Menu extends \Piwik\Plugin\Menu
{
    public function configureAdminMenu(MenuAdmin $menu): void
    {
        if (Piwik::hasUserSuperUserAccess()) {
            $menu->addMeasurableItem(
                Piwik::translate('BulkSiteDelete_MenuTitle'),
                $this->urlForAction('index'),
                $order = 35
            );
        }
    }
}
