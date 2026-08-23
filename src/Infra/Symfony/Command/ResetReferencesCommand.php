<?php

declare(strict_types=1);

namespace Infra\Symfony\Command;

use Doctrine\ORM\EntityManagerInterface;
use Infra\Symfony\Persistance\Doctrine\Entity\Reference;
use Infra\Symfony\Persistance\Doctrine\Repository\ReferenceRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-off command: wipes the `reference` table and re-fills it with the
 * authoritative historical data supplied by the club (1982-2010). Safe to
 * re-run: it always resets to this exact list, no duplication possible.
 *
 * Note: this source data has no city/lat/lng/icon breakdown (unlike the
 * previous placeholder content), so those fields are left empty — fill them
 * in via /admin/references if needed (e.g. for the map on /references).
 */
#[AsCommand(name: 'app:reset-references', description: 'Wipe and re-fill the reference table with the club-provided historical data (1982-2010)')]
class ResetReferencesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ReferenceRepository $referenceRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deleted = 0;
        foreach ($this->referenceRepository->findAll() as $reference) {
            $this->entityManager->remove($reference);
            ++$deleted;
        }
        $this->entityManager->flush();

        $rows = $this->rows();
        foreach ($rows as $row) {
            $reference = new Reference();
            $reference->setYear($row[0]);
            $reference->setCountry($row[1]);
            $reference->setName($row[2]);

            $this->entityManager->persist($reference);
        }
        $this->entityManager->flush();

        $io->success(sprintf('%d ancienne(s) référence(s) supprimée(s), %d nouvelle(s) référence(s) importée(s).', $deleted, count($rows)));

        return Command::SUCCESS;
    }

    /** @return array<int, array{0: int, 1: string, 2: string}> */
    private function rows(): array
    {
        return [
            [1982, 'URSS (en tournée)', 'Caucase, Géorgie, Arménie, Azeirbadjan'],
            [1984, 'Canada', "Festival Mondial de Drummondville (CIOFF), Québec (Fêtes du 445ème anniversaire), Acton Vale, Sorel (festival)"],
            [1986, 'Bulgarie', 'En tournée'],
            [1988, 'RFA', "Menden – Fêtes de jumelage avec Braine-l'Alleud."],
            [1988, 'Roumanie', "Bucarest – Sibiu, Curtea de Arges, Scornicesti. Tournée à l'invitation de l'Ensemble Calusul de Scornicesti."],
            [1989, 'Pologne', "Lublin, Nowy Targ. Tournée à l'invitation de L'Ensemble Folklorique de Zacopan"],
            [1989, 'Grande Bretagne', 'Basingstone – Rencontres Internationales.'],
            [1990, 'Hongrie', 'Sàrvàr – Festival International de Folklore (IOV).'],
            [1991, 'Italie', 'Pavullo – Festival International de Folklore.'],
            [1991, 'France', 'Caen – Représentation de la Province du Brabant.'],
            [1992, 'Belgique', 'Festival International de Moorsel.'],
            [1992, 'Belgique', 'Festival International de Bonheiden (CIOFF)'],
            [1993, 'Hongrie', '2ème participation au Festival International de Folklore de Sàrvàr (IOV).'],
            [1994, 'Belgique', 'Festival International de Folklore de Marcinelle (CIOFF)'],
            [1994, 'Allemagne', 'Essen – Fêtes de la Communauté Européenne.'],
            [1995, 'Belgique', 'Festival Mondial de Folklore de Saint Ghislain (CIOFF et IOV)'],
            [1995, 'Belgique', 'Festival International de Folklore en Brabant Wallon.'],
            [1995, 'France', "Draguignan – Représentation de la Belgique aux « Draguifolies » et dans plusieurs localités du Var."],
            [1996, 'Belgique', '2ème participation au Festival International de Folklore en Brabant Wallon.'],
            [1996, 'France', 'Busançay – Représentation de la Belgique aux Fêtes Traditionnelles de Busançay.'],
            [1996, 'Belgique', 'Nivelles : Participation à « Brabant Wallon en Fête ».'],
            [1997, 'Belgique', '3ème participation au Festival International de Folklore en Brabant Wallon.'],
            [1997, 'Belgique', 'Jodoigne : Participation à « Brabant Wallon en Fête ».'],
            [1998, 'Bulgarie', "Pazardjik, Panagurichté, Vélingrad, Saint Constantin, Calougérovo. Tournée à l'invitation de l'Ensemble Chavdar de Pazardjik."],
            [1998, 'Belgique', 'Rebecq : Participation à « Brabant Wallon en Fête ».'],
            [1999, 'Belgique', '2ème participation au festival (CIOFF) International de Folklore de Marcinelle.'],
            [1999, 'Belgique', 'Waterloo : Participation à « Brabant Wallon en Fête ».'],
            [2000, 'Hongrie', 'Sàrvàr : 3ème participation au festival (IOV) International de Folklore.'],
            [2000, 'Belgique', 'Hélecine : Participation à « Brabant Wallon en Fête ».'],
            [2001, 'Belgique', 'Rixensart : Brabant Wallon en Fête'],
            [2002, 'Belgique', "Braine-l'Alleud : Brabant Wallon en Fête"],
            [2003, 'Belgique', 'Grez Doiceau : Brabant Wallon en Fête'],
            [2004, 'Belgique', 'Genappe : Brabant Wallon en Fête'],
            [2005, 'Hongrie', ''],
            [2005, 'Belgique', 'Orp-le-Grand : Brabant Wallon en fête'],
            [2006, 'Tchéquie', 'Šlapanice : participation au festival (IOV) International de Folklore'],
            [2006, 'Belgique', 'Nivelles : Brabant Wallon en fête'],
            [2007, 'Grèce', 'Aigion :'],
            [2008, 'Belgique', 'Namur : Fête de Wallonie'],
            [2009, 'Belgique', 'Jodoigne : Brabant Wallon en fête'],
            [2010, 'Hongrie', 'Sàrvàr : 5ème participation au festival (IOV) International de Folklore'],
        ];
    }
}
