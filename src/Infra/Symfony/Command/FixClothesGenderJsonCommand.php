<?php

declare(strict_types=1);

namespace Infra\Symfony\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-off command: the `gender` column of `clothes_piece` and `clothes_costume`
 * is mapped as Doctrine `json` in the entities, but every row was still storing
 * the old PHP-serialized format (`a:1:{i:0;s:1:"F";}`), likely a leftover from
 * a previous Doctrine `array` column type that was switched to `json` without
 * migrating the existing data. Doctrine's JSON hydration chokes on that ("Could
 * not convert database value to json ... Syntax error") as soon as any query
 * hydrates a ClothesPiece/ClothesCostume entity.
 *
 * Safe to re-run: only rows starting with the PHP-serialize marker "a:" are
 * touched, and a re-run finds nothing left to fix.
 */
#[AsCommand(name: 'app:fix-clothes-gender-json', description: 'Convert the legacy PHP-serialized `gender` column (clothes_piece/clothes_costume) to valid JSON')]
class FixClothesGenderJsonCommand extends Command
{
    private const array TABLES = ['clothes_piece', 'clothes_costume'];

    public function __construct(private readonly Connection $connection)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        foreach (self::TABLES as $table) {
            $rows = $this->connection->fetchAllAssociative(
                "SELECT id, gender FROM {$table} WHERE gender LIKE 'a:%'"
            );

            $fixed = 0;
            $failed = 0;

            foreach ($rows as $row) {
                $decoded = @unserialize((string) $row['gender'], ['allowed_classes' => false]);

                if (false === $decoded && 'b:0;' !== $row['gender']) {
                    $io->warning(\sprintf('%s #%d: could not unserialize gender value, skipped.', $table, $row['id']));
                    $failed++;
                    continue;
                }

                $this->connection->update(
                    $table,
                    ['gender' => json_encode(array_values((array) $decoded))],
                    ['id' => $row['id']]
                );
                $fixed++;
            }

            $io->writeln(\sprintf('%s: %d row(s) fixed, %d skipped.', $table, $fixed, $failed));
        }

        $io->success('Done.');

        return Command::SUCCESS;
    }
}
