<?php

namespace Piwik\Plugins\BulkSiteDelete\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugins\SitesManager\API;

class DeleteBulk extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('site:delete-bulk');
        $this->setDescription('Delete multiple websites at once');
        $this->setHelp(
            "Delete multiple websites by providing site IDs as arguments.\n\n"
            . "Supports individual IDs, comma-separated lists, and ranges:\n"
            . "  ./console site:delete-bulk 5 12 23\n"
            . "  ./console site:delete-bulk 5,12,23\n"
            . "  ./console site:delete-bulk 10-20\n"
            . "  ./console site:delete-bulk 5 10-15 23\n"
            . "  ./console site:delete-bulk 5 12 --force"
        );
        $this->addRequiredArgument('ids', 'Site IDs to delete (space-separated, comma-separated, or ranges like 10-20)', null, true);
        $this->addNoValueOption('force', null, 'Skip confirmation prompt');
    }

    protected function doExecute(): int
    {
        $output = $this->getOutput();

        $siteIds = $this->parseSiteIds();

        if (empty($siteIds)) {
            $output->writeln('<error>No valid site IDs provided.</error>');
            return self::FAILURE;
        }

        // Verify which sites exist
        $accessibleSiteIds = API::getInstance()->getSitesIdWithAdminAccess();
        $validIds = [];
        $invalidIds = [];

        foreach ($siteIds as $id) {
            if (in_array($id, $accessibleSiteIds)) {
                $validIds[] = $id;
            } else {
                $invalidIds[] = $id;
            }
        }

        if (!empty($invalidIds)) {
            $output->writeln(sprintf(
                '<comment>Sites not found or no admin access: %s</comment>',
                implode(', ', $invalidIds)
            ));
        }

        if (empty($validIds)) {
            $output->writeln('<error>No valid sites to delete.</error>');
            return self::FAILURE;
        }

        // Display sites that will be deleted
        $output->writeln('');
        $output->writeln(sprintf('<info>Sites to delete (%d):</info>', count($validIds)));

        foreach ($validIds as $id) {
            try {
                $site = API::getInstance()->getSiteFromId($id);
                $output->writeln(sprintf('  [%d] %s', $id, $site['name']));
            } catch (\Exception $e) {
                $output->writeln(sprintf('  [%d] <comment>(could not fetch name)</comment>', $id));
            }
        }

        $output->writeln('');

        // Confirm unless --force
        if (!$this->getInput()->getOption('force')) {
            if (!$this->getInput()->isInteractive()) {
                $output->writeln('<error>Use --force to delete sites in non-interactive mode.</error>');
                return self::FAILURE;
            }

            $confirmed = $this->askForConfirmation(
                sprintf('<question>Delete these %d site(s)? This cannot be undone. (y/N)</question> ', count($validIds)),
                false
            );

            if (!$confirmed) {
                $output->writeln('Aborted.');
                return self::SUCCESS;
            }
        }

        // Delete each site
        $deleted = 0;
        $failed = 0;

        foreach ($validIds as $id) {
            try {
                API::getInstance()->deleteSite($id);
                $output->writeln(sprintf('<info>Deleted site %d</info>', $id));
                $deleted++;
            } catch (\Exception $e) {
                $output->writeln(sprintf('<error>Failed to delete site %d: %s</error>', $id, $e->getMessage()));
                $failed++;
            }
        }

        $output->writeln('');

        if ($failed === 0) {
            $this->writeSuccessMessage(sprintf('Successfully deleted %d site(s).', $deleted));
        } else {
            $output->writeln(sprintf('<comment>Deleted %d site(s), %d failed.</comment>', $deleted, $failed));
        }

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function parseSiteIds(): array
    {
        $rawArgs = $this->getInput()->getArgument('ids');
        $ids = [];

        foreach ($rawArgs as $arg) {
            // Split by comma
            $parts = explode(',', $arg);

            foreach ($parts as $part) {
                $part = trim($part);

                if ($part === '') {
                    continue;
                }

                // Check for range (e.g. 10-20)
                if (preg_match('/^(\d+)-(\d+)$/', $part, $matches)) {
                    $start = (int) $matches[1];
                    $end = (int) $matches[2];

                    if ($start > $end) {
                        $this->getOutput()->writeln(sprintf('<comment>Invalid range: %s (start > end), skipping.</comment>', $part));
                        continue;
                    }

                    if ($end - $start > 1000) {
                        $this->getOutput()->writeln(sprintf('<comment>Range too large: %s (max 1000), skipping.</comment>', $part));
                        continue;
                    }

                    for ($i = $start; $i <= $end; $i++) {
                        $ids[] = $i;
                    }
                } elseif (ctype_digit($part)) {
                    $ids[] = (int) $part;
                } else {
                    $this->getOutput()->writeln(sprintf('<comment>Invalid ID: %s, skipping.</comment>', $part));
                }
            }
        }

        return array_unique($ids);
    }
}
