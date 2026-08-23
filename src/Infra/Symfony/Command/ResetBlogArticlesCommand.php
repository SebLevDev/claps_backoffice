<?php

declare(strict_types=1);

namespace Infra\Symfony\Command;

use Doctrine\ORM\EntityManagerInterface;
use Infra\Symfony\Persistance\Doctrine\Entity\BlogArticle;
use Infra\Symfony\Persistance\Doctrine\Repository\BlogArticleRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * One-off command: wipes the `blog_article` table and re-fills it with the club's
 * real blog post history, extracted from a WordPress WXR export
 * (ensembleclap039sabots.WordPress.2026-06-29.xml, 60 published posts, 2009-2025).
 * Supersedes the earlier RSS-feed-based import (only had the last 10 posts).
 *
 * Content cleanup applied while parsing the export:
 *  - stripped Gutenberg block comments (<!-- wp:paragraph --> etc.)
 *  - stripped Facebook cross-post CSS class/dir cruft (many 2024-2025 posts are
 *    auto-imported from Facebook and came with huge generated class names)
 *  - replaced Facebook emoji <img> tags with their alt-text emoji character
 *  - re-applied WordPress' paragraph auto-formatting (wpautop) for older classic-editor
 *    posts whose stored content is plain text with blank-line paragraph breaks
 *  - corrected one corrupted post date (stored as year "0209", clearly meant "2009"
 *    given the post's content refers to events in 2008/2009)
 *  - featured image resolved via _thumbnail_id postmeta -> attachment URL, falling
 *    back to the first <img> found in the content when no thumbnail is set
 *
 * Idempotent: safe to re-run, always resets to this exact 60-post list.
 */
#[AsCommand(name: 'app:reset-blog-articles', description: "Wipe and re-fill blog_article with the club's real post history from the WordPress WXR export")]
class ResetBlogArticlesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly BlogArticleRepository $blogArticleRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $deleted = 0;
        foreach ($this->blogArticleRepository->findAll() as $article) {
            $this->entityManager->remove($article);
            ++$deleted;
        }
        $this->entityManager->flush();

        $rows = $this->rows();
        foreach ($rows as $row) {
            $article = new BlogArticle();
            $article->setSlug($row['slug']);
            $article->setTag($row['tag']);
            $article->setTitle($row['title']);
            $article->setDate(new \DateTime($row['date']));
            $article->setResume($row['resume']);
            $article->setContent($row['content']);
            $article->setImage($row['image']);
            $article->setIsPublished(true);
            $article->setUpdatedAt(new \DateTime());

            $this->entityManager->persist($article);
        }
        $this->entityManager->flush();

        $io->success(sprintf('%d ancien(s) article(s) supprimé(s), %d nouvel(le)s article(s) importé(s).', $deleted, count($rows)));

        return Command::SUCCESS;
    }

    /** @return array<int, array<string, string|null>> */
    private function rows(): array
    {
        return [
            [
                'slug' => 'deux-merites-pour-clapsabots',
                'tag' => 'Actualité',
                'title' => 'Deux Mérites pour Clap\'Sabots !',
                'date' => '2025-06-12 17:55:55',
                'resume' => 'Quelle soirée inoubliable hier lors de la cérémonie des Mérites Culturels de la Commune de Braine-l\'Alleud - page officielle Nous sommes fiers et émus d’avoir remporté non pas un, mais deux prix : Le Mérite Culturel - Service rendu à la vie culturelle, décerné par le jury ❤️ Le...',
                'content' => '<div>
<div>Quelle soirée inoubliable hier lors de la cérémonie des Mérites Culturels de la <a tabindex="0" role="link" href="https://www.facebook.com/commune.braine.lalleud?__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=-]K-R"><span><span>Commune de Braine-l\'Alleud - page officielle</span></a></span></div>
</div>
<div>
<div>Nous sommes fiers et émus d’avoir remporté non pas un, mais deux prix  :</div>
</div>
<div>
<div> Le Mérite Culturel - Service rendu à la vie culturelle, décerné par le jury</div>
<div>❤️ Le Prix du Public, grâce à vos nombreux votes et votre incroyable soutien !</div>
</div>
<div>
<div>Un immense merci à tous ceux qui ont voté, partagé, encouragé… et bien sûr à tous les bénévoles, danseurs et amis qui font vivre Clap\'Sabots depuis bientôt 50 ans !</div>
<div>Merci à la Maison de la Culture de Braine-l\'Alleud</div>
<div>On continue à faire vibrer les planches au rythme des danses du monde ✨</div>
</div>
<div>
<div><a tabindex="0" role="link" href="https://www.facebook.com/hashtag/clapsabots?__eep__=6&amp;__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=*NK-R">#ClapSabots</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/m%C3%A9ritesculturels?__eep__=6&amp;__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=*NK-R">#MéritesCulturels</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/brainelalleud?__eep__=6&amp;__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=*NK-R">#BraineLAlleud</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/folklorevivants?__eep__=6&amp;__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=*NK-R">#FolkloreVivants</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/dansetraditionnelle?__eep__=6&amp;__cft__[0]=AZWuFVVgkEN2_JgQLgVxmbTRvWTcsrRMwiXV-BG-_4ltjKL7_FZoJde8bGwzvEIWhqvdQMRSvDSflLC71Y7dXncR4EsCQd2LhVIr0HNeCRMVa3wdxjDKbIJVUNbuuIE0t_b2CvS_WV94ool9AUgs9-Ykgv81uy4hF88IWw3I_FRjr57JfENlHblr1RxJDIfUs35V_nqnun6cJVCeE5dY-eF0&amp;__tn__=*NK-R">#dansetraditionnelle</a></div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/06/505991566_1375206836884447_1106290719695484600_n.jpg',
            ],
            [
                'slug' => 'lete-des-festivals-folkloriques-en-wallonie',
                'tag' => 'Actualité',
                'title' => 'L’été des festivals folkloriques en Wallonie !',
                'date' => '2025-06-02 17:53:43',
                'resume' => 'Le folklore ne prend pas de vacances — bien au contraire ! ☀️ Chaque été, la Wallonie s’anime au rythme des musiques et danses du monde entier, grâce à de magnifiques festivals qui font vibrer nos villes et nos cœurs. Cette année encore, nous tenons à saluer et soutenir ces...',
                'content' => '<div>
<div>Le folklore ne prend pas de vacances — bien au contraire ! ☀️</div>
</div>
<div>
<div>Chaque été, la Wallonie s’anime au rythme des musiques et danses du monde entier, grâce à de magnifiques festivals qui font vibrer nos villes et nos cœurs. Cette année encore, nous tenons à saluer et soutenir ces événements qui mettent à l\'honneur les cultures venues d’ailleurs, et qui font rayonner les traditions populaires avec passion et générosité.</div>
</div>
<div>
<div>Voici quelques rendez-vous immanquables de l’été :</div>
</div>
<div>
<div> Festival Mondial de Folklore de St Ghislain</div>
<div> Du 16 au 22 juin 2025</div>
<div>Une semaine de spectacles hauts en couleurs avec des troupes venues des quatre coins du globe.</div>
</div>
<div>
<div> Macadanse – Festival folklorique de Wavre</div>
<div> Vendredi 27 juin 2025</div>
<div>Cette année, cap sur le Brésil  !</div>
</div>
<div>
<div> <a tabindex="0" role="link" href="https://www.facebook.com/profile.php?id=100063500322402&amp;__cft__[0]=AZXAC_njOCw_9FJZjETLq-yHvq2cS6s_bnpL4eM7W9iyjQNR6JlyrFY2cIN_H_ZxEkm_0x7kOBNFoutyDnvR5VNQtFSTlEYGnsY5Liz2OVK9Afh9KS1ctHEECaD7YJn9UVnrvP03D0_eZYjyz3pF6LfQ7ddoLTqGZiJKitfywPmc-gKNRxAN0UXMOIG8mq0_z5qIMV5SqokgqFlZJPx-5ZAo&amp;__tn__=-]K-R"><span><span>Festival Mondial de Folklore de Jambes-Namur asbl</span></a></span></div>
<div> Du 15 au 18 août 2025</div>
<div>Un grand classique du folklore international, en bord de Meuse, qui fait voyager le public sans quitter la Wallonie.</div>
</div>
<div>
<div> En tant que passionnés de danses traditionnelles, nous apportons tout notre soutien à ces festivals et à leurs organisateurs. Merci à eux de faire vivre le folklore avec autant d’enthousiasme !</div>
<div><a tabindex="0" role="link" href="https://www.facebook.com/hashtag/dansestraditionnelles?__eep__=6&amp;__cft__[0]=AZXAC_njOCw_9FJZjETLq-yHvq2cS6s_bnpL4eM7W9iyjQNR6JlyrFY2cIN_H_ZxEkm_0x7kOBNFoutyDnvR5VNQtFSTlEYGnsY5Liz2OVK9Afh9KS1ctHEECaD7YJn9UVnrvP03D0_eZYjyz3pF6LfQ7ddoLTqGZiJKitfywPmc-gKNRxAN0UXMOIG8mq0_z5qIMV5SqokgqFlZJPx-5ZAo&amp;__tn__=*NK-R">#DansesTraditionnelles</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/festival?__eep__=6&amp;__cft__[0]=AZXAC_njOCw_9FJZjETLq-yHvq2cS6s_bnpL4eM7W9iyjQNR6JlyrFY2cIN_H_ZxEkm_0x7kOBNFoutyDnvR5VNQtFSTlEYGnsY5Liz2OVK9Afh9KS1ctHEECaD7YJn9UVnrvP03D0_eZYjyz3pF6LfQ7ddoLTqGZiJKitfywPmc-gKNRxAN0UXMOIG8mq0_z5qIMV5SqokgqFlZJPx-5ZAo&amp;__tn__=*NK-R">#festival</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/dapo?__eep__=6&amp;__cft__[0]=AZXAC_njOCw_9FJZjETLq-yHvq2cS6s_bnpL4eM7W9iyjQNR6JlyrFY2cIN_H_ZxEkm_0x7kOBNFoutyDnvR5VNQtFSTlEYGnsY5Liz2OVK9Afh9KS1ctHEECaD7YJn9UVnrvP03D0_eZYjyz3pF6LfQ7ddoLTqGZiJKitfywPmc-gKNRxAN0UXMOIG8mq0_z5qIMV5SqokgqFlZJPx-5ZAo&amp;__tn__=*NK-R">#dapo</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/folklore?__eep__=6&amp;__cft__[0]=AZXAC_njOCw_9FJZjETLq-yHvq2cS6s_bnpL4eM7W9iyjQNR6JlyrFY2cIN_H_ZxEkm_0x7kOBNFoutyDnvR5VNQtFSTlEYGnsY5Liz2OVK9Afh9KS1ctHEECaD7YJn9UVnrvP03D0_eZYjyz3pF6LfQ7ddoLTqGZiJKitfywPmc-gKNRxAN0UXMOIG8mq0_z5qIMV5SqokgqFlZJPx-5ZAo&amp;__tn__=*NK-R">#folklore</a></div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/06/502969916_1367971000941364_5263944395663274684_n.jpg',
            ],
            [
                'slug' => 'europe-day-2025',
                'tag' => 'Spectacle',
                'title' => 'Europe Day 2025',
                'date' => '2025-05-11 10:33:33',
                'resume' => 'Hier, nous avons eu l’honneur de danser devant la Commission européenne à l’occasion de Europe Day 2025, et quelle ambiance ! Un grand merci au public présent pour vos applaudissements chaleureux, et bravo à nos danseurs pour cette belle prestation aux couleurs de la Bulgarie,...',
                'content' => '<div>
<div> Hier, nous avons eu l’honneur de danser devant la Commission européenne à l’occasion de Europe Day 2025, et quelle ambiance !</div>
</div>
<div>
<div>Un grand merci au public présent pour vos applaudissements chaleureux, et bravo à nos danseurs pour cette belle prestation aux couleurs de  la Bulgarie,  la Tchéquie et  l’Ukraine ✨</div>
</div>
<div>
<div>Bon dimanche à tous et bonne fête des mamans.</div>
</div>
<div></div>
<div><img src="https://www.clapsabots.be/wp-content/uploads/2025/05/495281790_1352293229175808_4760650155774558570_n.jpg" alt="" width="982" height="982" /> <img src="https://www.clapsabots.be/wp-content/uploads/2025/05/495544840_1352294265842371_8290863322093215728_n.jpg" alt="" width="910" height="910" /> <img src="https://www.clapsabots.be/wp-content/uploads/2025/05/496055334_1352293312509133_802486606935830522_n-1024x1024.jpg" alt="" width="1024" height="1024" /> <img src="https://www.clapsabots.be/wp-content/uploads/2025/05/495339686_1352293249175806_8301487789371284333_n-1024x1024.jpg" alt="" width="1024" height="1024" /> <img src="https://www.clapsabots.be/wp-content/uploads/2025/05/496093799_1352293295842468_5983951374663520160_n-1024x1024.jpg" alt="" width="1024" height="1024" /></div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/05/495339686_1352293249175806_8301487789371284333_n.jpg',
            ],
            [
                'slug' => 'europaday-2025-on-y-sera',
                'tag' => 'Spectacle',
                'title' => 'EuropaDay 2025 — On y sera !',
                'date' => '2025-05-07 08:38:49',
                'resume' => 'Ce samedi à 18h00, nous monterons sur scène devant la Commission européenne (Rue de la Loi 200, Bruxelles) pour une prestation de 20 minutes dans le cadre de la Journée de l’Europe. Au programme : des danses traditionnelles de Bulgarie — Tchéquie — Ukraine Programme complet...',
                'content' => '<div>Ce samedi à 18h00, nous monterons sur scène devant la Commission européenne (Rue de la Loi 200, Bruxelles) pour une prestation de 20 minutes dans le cadre de la Journée de l’Europe.</div>
<div>Au programme : des danses traditionnelles de  Bulgarie —  Tchéquie —  Ukraine </div>
<div>Programme complet : <a tabindex="0" role="link" href="https://commission.europa.eu/get-involved/visit-european-commission/europe-day-2025_en?fbclid=IwZXh0bgNhZW0CMTAAYnJpZBExcnROcTFjTVVnaEltd0VIRAEeBs-EHtqUkN0aB4enzocUZ14S4rSIAgXLFK_9dpCHTWP4A2O5pJV0Nlzc9lA_aem_3P6m7yWnUMdoztBzK6dZxg" target="_blank" rel="nofollow noopener noreferrer">https://commission.europa.eu/.../visit.../europe-day-2025_en</a></div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/05/495367894_1349641026107695_6071085645811486815_n.jpg',
            ],
            [
                'slug' => 'seance-portes-ouvertes',
                'tag' => 'Animation',
                'title' => 'Séance portes ouvertes',
                'date' => '2025-04-28 08:40:10',
                'resume' => 'Après une semaine de repos et un week-end de détente à Wellin, nous sommes tous ressourcés et prêts à vous accueillir ce lundi pour une séance portes ouvertes ! Venez nous rendre visite et découvrir les danses folkloriques d’ici et d’ailleurs. Rendez-vous de 20h à 22h !',
                'content' => '<div>Après une semaine de repos et un week-end de détente à Wellin, nous sommes tous ressourcés et prêts à vous accueillir ce lundi pour une séance portes ouvertes !</div>
<div>Venez nous rendre visite et découvrir les danses folkloriques d’ici et d’ailleurs.</div>
<div>Rendez-vous de 20h à 22h !</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/05/492226835_1343045163433948_880673912068500629_n.jpg',
            ],
            [
                'slug' => 'gala-2025-merci',
                'tag' => 'Spectacle',
                'title' => 'Gala 2025 - Merci',
                'date' => '2025-04-21 08:41:23',
                'resume' => 'Merci à vous, cher public, pour votre soutien et vos applaudissements ! Vous étiez présents en nombre durant ces deux jours de représentation. Nous espérons que vous avez voyagé agréablement à travers les danses et les costumes des différents pays. Nous espérons vous revoir...',
                'content' => '<div>Merci à vous, cher public, pour votre soutien et vos applaudissements !</div>
<div>Vous étiez présents en nombre durant ces deux jours de représentation.</div>
<div>Nous espérons que vous avez voyagé agréablement à travers les danses et les costumes des différents pays.</div>
<div>Nous espérons vous revoir bientôt, lors de prochains événements !</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2025/05/490053964_1337848537286944_5103610977850178556_n.jpg',
            ],
            [
                'slug' => 'la-billetterie-est-ouverte',
                'tag' => 'Actualité',
                'title' => 'La billetterie est ouverte !',
                'date' => '2025-02-05 13:50:02',
                'resume' => 'Les ventes sont officiellement lancées ! Réservez dès maintenant vos places pour le samedi 19 avril et/ou le dimanche 20 avril ✨ Astuce : Profitez de la prévente pour réserver aussi votre carte boissons et éviter les files d’attente au bar ! Réservez vite vos billets ici...',
                'content' => '<div>
<div>Les ventes sont officiellement lancées ! Réservez dès maintenant vos places pour le samedi 19 avril et/ou le dimanche 20 avril ✨</div>
</div>
<div>
<div> Astuce : Profitez de la prévente pour réserver aussi votre carte boissons et éviter les files d’attente au bar ! </div>
<div></div>
</div>
<div>
<div> Réservez vite vos billets ici <a tabindex="0" role="link" href="https://ticketing.byemisys.com/gala-de-danses-traditionneles/official/fr/tickets?fbclid=IwZXh0bgNhZW0CMTAAAR3I69MUb82ZKwJSH01Uq7xGU95aUMZMvcmUNDy1v2S5v3Skw4UmuM5g8GA_aem_eFBnyc-w-tIwGUkCRAtEGw" target="_blank" rel="nofollow noopener noreferrer"><span>https://ticketing.byemisys.com/gala-de-danses-traditionneles/official/fr/ticket</a></span>s</div>
</div>
<div>
<div>Ne tardez pas, les places sont limitées ! </div>
<div></div>
</div>
<div>
<div><a tabindex="0" role="link" href="https://www.facebook.com/hashtag/galadedanses?__eep__=6&amp;__cft__[0]=AZXsGCMW980lXzpVsu4GnvM_5LPgJVcITeISFnAyaGtAhZwCtwHnAHMet-N6u-a9XuBzH-9mFABgIryisk3V2kDpCjexdEy9UkOGdfPfXZXisaEqQwcTj8HZG-ddaHYTiCT4NehP7X8EAxZN9TujuFtX&amp;__tn__=*NK-R">#GalaDeDanses</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/billetterieouverte?__eep__=6&amp;__cft__[0]=AZXsGCMW980lXzpVsu4GnvM_5LPgJVcITeISFnAyaGtAhZwCtwHnAHMet-N6u-a9XuBzH-9mFABgIryisk3V2kDpCjexdEy9UkOGdfPfXZXisaEqQwcTj8HZG-ddaHYTiCT4NehP7X8EAxZN9TujuFtX&amp;__tn__=*NK-R">#BilletterieOuverte</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/r%C3%A9servezvosplaces?__eep__=6&amp;__cft__[0]=AZXsGCMW980lXzpVsu4GnvM_5LPgJVcITeISFnAyaGtAhZwCtwHnAHMet-N6u-a9XuBzH-9mFABgIryisk3V2kDpCjexdEy9UkOGdfPfXZXisaEqQwcTj8HZG-ddaHYTiCT4NehP7X8EAxZN9TujuFtX&amp;__tn__=*NK-R">#RéservezVosPlaces</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/%C3%A9vitezlesfiles?__eep__=6&amp;__cft__[0]=AZXsGCMW980lXzpVsu4GnvM_5LPgJVcITeISFnAyaGtAhZwCtwHnAHMet-N6u-a9XuBzH-9mFABgIryisk3V2kDpCjexdEy9UkOGdfPfXZXisaEqQwcTj8HZG-ddaHYTiCT4NehP7X8EAxZN9TujuFtX&amp;__tn__=*NK-R">#ÉvitezLesFiles</a></div>
</div>',
                'image' => null,
            ],
            [
                'slug' => 'gala-2025-save-the-date',
                'tag' => 'Spectacle',
                'title' => 'Gala 2025 - Save the date',
                'date' => '2024-10-07 11:24:37',
                'resume' => 'Deux jours de festivités pour encore plus d’émerveillement Cette année, nous avons une petite nouveauté. Le spectacle aura lieu à la salle “De Put” du centre Destelheide à Dworp, qui dispose de 200 places en gradin. Pour pouvoir accueillir tout le monde, nous effectuerons donc...',
                'content' => 'Deux jours de festivités pour encore plus d’émerveillement Cette année, nous avons une petite nouveauté.

Le spectacle aura lieu à la salle “<em>De Put</em>” du centre <strong>Destelheide à Dworp</strong>, qui dispose de 200 places en gradin.

Pour pouvoir accueillir tout le monde, nous effectuerons donc deux représentations : ·
<ul>
 	<li><strong>Samedi 19 avril 2025</strong> : Répétition l\'après-midi, spectacle le soir suivi d\'un moment convivial au foyer et à la cafétéria. ·</li>
 	<li><strong>Dimanche 20 avril 2025</strong> : Seconde représentation dans l\'après-midi. Nous pourrons profiter de la terrasse si le beau temps nous accompagne.</li>
</ul>
Nous pourrons laisser nos costumes sur place après le spectacle du samedi, ce qui nous permettra de profiter pleinement de la soirée sans avoir à nous préoccuper du rangement.

Nous reviendrons vers vous en début d\'année prochaine avec plus de détails, mais je vous conseille d\'ores et déjà de bloquer ces dates dans votre agenda et dans celui de vos proches.',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/10/PXL_20241003_131514566.MP-1-scaled.jpg',
            ],
            [
                'slug' => 'reprise-des-cours-2024-2025',
                'tag' => 'Actualité',
                'title' => 'Reprise des cours',
                'date' => '2024-09-01 18:28:43',
                'resume' => 'Les cours reprennent ce vendredi 6 septembre et le lundi 9 septembre 2024',
                'content' => '<p>Les cours reprennent ce vendredi 6 septembre et le lundi 9 septembre 2024</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2016/11/IMG_9566.jpg',
            ],
            [
                'slug' => 'retour-sur-notre-participation-au-festival-mondial-de-folklore',
                'tag' => 'Actualité',
                'title' => 'Retour sur notre participation au Festival Mondial de Folklore',
                'date' => '2024-08-20 10:30:41',
                'resume' => '[cmsmasters_row][cmsmasters_column data_width="1/1"][cmsmasters_text] Retour sur notre participation au 62ème Festival Mondial de Folklore de Jambes-Namur Le 17 août 2024 a été une journée marquante pour l\'Ensemble Clap’Sabots. Invités à représenter le folklore wallon lors du...',
                'content' => '<p>[cmsmasters_row][cmsmasters_column data_width="1/1"][cmsmasters_text]</p>
<p><strong>Retour sur notre participation au 62ème Festival Mondial de Folklore de Jambes-Namur</strong></p>
<p>Le 17 août 2024 a été une journée marquante pour l\'Ensemble Clap’Sabots. Invités à représenter le folklore wallon lors du 62ème Festival Mondial de Folklore de Jambes-Namur, nous avons eu la chance de partager notre culture dans un cadre unique, entourés de groupes venus de Bosnie-Herzégovine, de Grèce, du Japon, du Pérou, de Taïwan et de Belgique.</p>
<p>Depuis quatre mois, nous préparions ce moment en collaboration avec l\'ensemble musical Mish-Mash, et c\'est avec beaucoup d\'excitation que nous avons rejoint Namur ce samedi matin. Accueillis à 9h30 par notre guide, nous avons enfilé nos costumes à l\'Institut technique de Namur avant de prendre le bus pour le centre-ville, en compagnie des autres groupes de danse.</p>
<p>La journée a commencé par un cortège musical sur le marché du Bord de l\'eau, le long de la Sambre, entre 10h30 et 12h00. Ce fut un moment convivial, où les passants ont pu découvrir les différentes cultures présentes au festival. Sur la place Maurice Servais, chaque groupe a ensuite offert une courte prestation de deux minutes, offrant ainsi un avant-goût de la diversité culturelle présente sur le festival.</p>
<p>À midi, nous avons participé à une réception officielle dans les Jardins du Maïeur, où chaque groupe a présenté une danse devant les officiels. Pour clôturer notre prestation, nous avons dansé un Menuet de la chaîne. Mais le moment le plus mémorable fut sans doute lorsque nous avons invité les autres groupes, ainsi que le bourgmestre de Namur, Monsieur Maxime Prévot, à danser un cercle circassien avec nous. Quel échange !</p>
<p>L\'après-midi, bien que le programme prévoyait de remonter dans les bus, l\'enthousiasme général a pris le dessus. La rue est devenue notre scène, où des improvisations inattendues ont vu le jour. Bosniaques, Grecs, Belges et Péruviens se sont unis, dansant ensemble au son de l\'orchestre bosniaque. Ce moment spontané a été l\'une des plus belles illustrations de l\'esprit du festival : l\'union par la danse.</p>
<p>Le soir, nous avons eu l’honneur d’ouvrir la cérémonie officielle avec une suite de huit danses wallonnes. Une fois sur scène, nous avons pleinement vécu l’instant, portés par l’énergie du public qui a accueilli notre prestation avec enthousiasme.</p>
<p>Nous souhaitons exprimer notre gratitude aux organisateurs du festival pour leur invitation et leur accueil chaleureux. Grâce à des événements comme celui-ci, le folklore et les traditions culturelles continuent de vivre et de se transmettre. Nous espérons sincèrement que ce type de festival perdurera, car il offre une occasion précieuse de rencontres, d’échanges et de célébration de la diversité culturelle. C’est en préservant ces moments que nous assurons l’avenir de nos traditions et de notre passion</p>
<p>[/cmsmasters_text][cmsmasters_gallery layout="gallery" gallery_type="masonry" image_size_gallery="full" gallery_columns="4" gallery_links="lightbox" animation_delay="0"]9396|https://www.clapsabots.be/wp-content/uploads/2024/08/457748294_491224303665920_4224329393895256011_n-1-150x150.jpg,9395|https://www.clapsabots.be/wp-content/uploads/2024/08/457403244_491224286999255_3852564329436770592_n-150x150.jpg,9394|https://www.clapsabots.be/wp-content/uploads/2024/08/457591068_491224293665921_2978198231304719397_n-150x150.jpg,9393|https://www.clapsabots.be/wp-content/uploads/2024/08/457661467_491224300332587_7865281410044300643_n-150x150.jpg,9392|https://www.clapsabots.be/wp-content/uploads/2024/08/458077788_491224306999253_5547913817303447270_n-150x150.jpg,9391|https://www.clapsabots.be/wp-content/uploads/2024/08/457559817_491224310332586_7863135834341504777_n-150x150.jpg,9390|https://www.clapsabots.be/wp-content/uploads/2024/08/457476773_491224290332588_1409011600446464285_n-150x150.jpg,9388|https://www.clapsabots.be/wp-content/uploads/2024/08/457748294_491224303665920_4224329393895256011_n-150x150.jpg[/cmsmasters_gallery][cmsmasters_text]</p>
<p>&nbsp;</p>
<p>[/cmsmasters_text][/cmsmasters_column][/cmsmasters_row]</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/08/457748294_491224303665920_4224329393895256011_n.jpg',
            ],
            [
                'slug' => 'participationau-62eme-festival-mondial-de-folklore-de-jambes-namur',
                'tag' => 'Actualité',
                'title' => 'Participationau 62ème Festival Mondial de Folklore de Jambes-Namur !',
                'date' => '2024-07-25 11:18:35',
                'resume' => 'Chers amis et passionnés de folklore, Nous avons le plaisir de vous annoncer que l\'Ensemble Clap’Sabots participera au 62ème Festival Mondial de Folklore de Jambes-Namur, qui se tiendra le samedi 17 août 2024. Invités à représenter fièrement le folklore wallon, nous aurons...',
                'content' => 'Chers amis et passionnés de folklore,

Nous avons le plaisir de vous annoncer que l\'Ensemble Clap’Sabots participera au 62ème Festival Mondial de Folklore de Jambes-Namur, qui se tiendra le samedi 17 août 2024. Invités à représenter fièrement le folklore wallon, nous aurons l\'opportunité de partager notre culture dans un cadre exceptionnel, aux côtés de groupes venus de Bosnie-Herzégovine, de Grèce, du Japon, du Pérou, de Taïwan, et bien sûr, d\'autres régions de Belgique.

Depuis plusieurs mois, nous préparons cet événement avec l\'ensemble musical Mish-Mash, et c\'est avec une immense joie que nous nous rendrons à Namur le samedi 17 aout pour cette journée de fête et de partage.

<strong>Programme de la journée :</strong>
<ul>
 	<li><strong>Dès 10h30</strong>, nous participerons à un cortège musical au marché du Bord de l\'eau, le long de la Sambre. Ce défilé sera l\'occasion de découvrir les différentes cultures représentées au festival dans une ambiance conviviale.</li>
 	<li><strong>À midi</strong>, nous aurons l\'honneur de participer à une réception officielle dans les Jardins du Maïeur, où chaque groupe présentera une danse devant les officiels. Nous clôturerons notre prestation avec un Menuet de la chaîne.</li>
 	<li><strong>Le soir</strong>, nous aurons l\'honneur d\'ouvrir la cérémonie officielle du festival avec une suite de huit danses wallonnes. Ce sera un moment fort, et nous comptons sur votre présence pour partager cette expérience unique.</li>
</ul>
Nous espérons vous voir nombreux à cet événement, pour célébrer ensemble la richesse et la diversité des cultures du monde. Ne manquez pas cette occasion de vivre un moment de rencontre et de partage autour de nos traditions.

À très bientôt au Festival de Jambes-Namur !',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/07/454526755_1028187089307959_5481674168934835054_n.jpg',
            ],
            [
                'slug' => 'fetes-de-la-saint-jean-lillois-braine-alleud',
                'tag' => 'Spectacle',
                'title' => 'Fêtes de la Saint-Jean Lillois',
                'date' => '2024-07-07 11:03:40',
                'resume' => '[cmsmasters_row][cmsmasters_column data_width="1/1"][cmsmasters_text] Le dimanche 30 juin 2024 se déroulaient les fêtes de la Saint-Jean de Lillois. Après plusieurs années sans participation, nous avons été de nouveau sollicités pour prendre part aux Fêtes de la Saint-Jean de...',
                'content' => '<p>[cmsmasters_row][cmsmasters_column data_width="1/1"][cmsmasters_text]</p>
<p><strong><em>Le dimanche 30 juin 2024 se déroulaient les fêtes de la Saint-Jean de Lillois.</em></strong></p>
<p>Après plusieurs années sans participation, nous avons été de nouveau sollicités pour prendre part aux Fêtes de la Saint-Jean de Lillois.</p>
<p>Cette année, ce sont les sections des adultes et des jeunes qui ce sont mobilisées pour représenter notre groupe. Juste après le défilé des chars de la Saint-Jean, nos danseurs ont mis en lumière le folklore de la Bulgarie et de la Chine à travers des chorégraphies dynamiques et colorées.</p>
<p>Le public, captivé dès les premières notes, s\'est montré très réceptif. Mais ce n\'est pas tout ! Entre nos deux représentations, nous avons eu l\'occasion d\'animer la foule avec plusieurs danses d’animations. À notre grande surprise, le public s\'est levé de ses chaises et ne voulait plus s\'arrêter de danser avec nous. Ce fut un véritable succès !</p>
<p>Nous sommes ravis d’avoir pu retrouver cette fête emblématique et d’avoir partagé ces moments avec les habitants de la commune. Une expérience que nous espérons bien réitérer à l\'avenir.</p>
<p>&nbsp;</p>
<p>Photos : MDB Photography</p>
<p>[/cmsmasters_text][cmsmasters_gallery layout="gallery" gallery_type="masonry" gallery_count="15" gallery_padding="0" image_size_gallery="full" gallery_columns="4" gallery_links="lightbox" animation_delay="0"]9412|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-517-150x150.jpg,9413|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-518-150x150.jpg,9411|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-516-150x150.jpg,9410|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-525-150x150.jpg,9409|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-524-150x150.jpg,9408|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-523-150x150.jpg,9403|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-518-Copie-150x150.jpg,9404|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-519-150x150.jpg,9405|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-520-150x150.jpg,9406|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-521-150x150.jpg,9407|https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-522-150x150.jpg[/cmsmasters_gallery][/cmsmasters_column][/cmsmasters_row]</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/10/Saint-Jean-2024-524-scaled.jpg',
            ],
            [
                'slug' => 'merci-pour-votre-presence',
                'tag' => 'Actualité',
                'title' => 'Merci pour votre présence',
                'date' => '2024-04-05 11:16:43',
                'resume' => '✨ Tous les membres Clap\'Sabots tiennent à vous remercier chaleureusement pour votre présence nombreuse lors de notre soirée spectacle de ce samedi 30 mars ! Nous sommes d\'ores et déjà déterminés à vous offrir une expérience encore plus incroyable l\'année prochaine. Pour y...',
                'content' => '<div>
<div>&#x2728; Tous les membres Clap\'Sabots tiennent à vous remercier chaleureusement pour votre présence nombreuse lors de notre soirée spectacle de ce samedi 30 mars !</div>
</div>
<div>
<div>Nous sommes d\'ores et déjà déterminés à vous offrir une expérience encore plus incroyable l\'année prochaine.</div>
<div>Pour y parvenir, nous souhaitons connaitre votre avis :</div>
<div>https://docs.google.com/forms/d/e/1FAIpQLSetUHjm5fLlnSxP3lIKitN7S6Ug3A_e8D3cl3fVs2cgHAJ3gg/viewform?usp=send_form</div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2015/04/23_Clapsabots_08_008_Inde-scaled.jpg',
            ],
            [
                'slug' => 'annonce-billetterie',
                'tag' => 'Actualité',
                'title' => 'Annonce billetterie',
                'date' => '2024-02-06 11:14:58',
                'resume' => 'Cette année, la vente de tickets pour notre Gala annuel de danses traditionnelles – 47ème anniversaire du 30 mars à Nivelles passe à la vitesse supérieure avec une billetterie en ligne ! 🎟️✨ Grâce à cette technologie moderne, vous pouvez désormais profiter de nombreux avantages,...',
                'content' => '<div>
<div>Cette année, la vente de tickets pour notre <a tabindex="0" role="link" href="https://www.facebook.com/events/2817987705016827/?__cft__[0]=AZWD5GGbC9nO_IRf6dh0hROnQq3xE5ydBPVFyH6IY2N14_9plIdwNdIXtESg_NqjSjpZWThWfPMwFkRhnnsoMs3hRLUQCOorawECdbCBk1XyfytOCvgmXg2HKRmHqO3-tuEW3cvzkwYi-y2qO_Qj0DnETM1BmjBkMtbqq-ortvQsSsns3nUALMcnOOsRUxNluZRJ0bUb7pt4d8eMxBEC4MNB&amp;__tn__=-UK-R"><span>Gala annuel de danses traditionnelles – 47ème anniversaire</a></span> du 30 mars à Nivelles passe à la vitesse supérieure avec une billetterie en ligne ! &#x1f39f;&#xfe0f;&#x2728;</div>
</div>
<div>
<div>Grâce à cette technologie moderne, vous pouvez désormais profiter de nombreux avantages, notamment le choix de vos places en temps réel, avec une vue instantanée sur l\'occupation de la salle. &#x1f3ad;&#x1f4bb;</div>
</div>
<div>
<div>Un immense merci à la société <a tabindex="0" role="link" href="https://www.facebook.com/emisys?__cft__[0]=AZWD5GGbC9nO_IRf6dh0hROnQq3xE5ydBPVFyH6IY2N14_9plIdwNdIXtESg_NqjSjpZWThWfPMwFkRhnnsoMs3hRLUQCOorawECdbCBk1XyfytOCvgmXg2HKRmHqO3-tuEW3cvzkwYi-y2qO_Qj0DnETM1BmjBkMtbqq-ortvQsSsns3nUALMcnOOsRUxNluZRJ0bUb7pt4d8eMxBEC4MNB&amp;__tn__=-]K-R"><span>Emisys</a></span> pour cette fonctionnalité exceptionnelle ! &#x1f64c; Nous sommes ravis de voir que déjà 300 places ont trouvé leurs heureux propriétaires. &#x1f389;</div>
</div>
<div>
<div>Réservez vos places tant qu\'il y en a, et préparez-vous à une soirée de spectacles inoubliable ! &#x1f308;&#x1f3b6; <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/danse?__eep__=6&amp;__cft__[0]=AZWD5GGbC9nO_IRf6dh0hROnQq3xE5ydBPVFyH6IY2N14_9plIdwNdIXtESg_NqjSjpZWThWfPMwFkRhnnsoMs3hRLUQCOorawECdbCBk1XyfytOCvgmXg2HKRmHqO3-tuEW3cvzkwYi-y2qO_Qj0DnETM1BmjBkMtbqq-ortvQsSsns3nUALMcnOOsRUxNluZRJ0bUb7pt4d8eMxBEC4MNB&amp;__tn__=*NK-R">#danse</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/traditionnelle?__eep__=6&amp;__cft__[0]=AZWD5GGbC9nO_IRf6dh0hROnQq3xE5ydBPVFyH6IY2N14_9plIdwNdIXtESg_NqjSjpZWThWfPMwFkRhnnsoMs3hRLUQCOorawECdbCBk1XyfytOCvgmXg2HKRmHqO3-tuEW3cvzkwYi-y2qO_Qj0DnETM1BmjBkMtbqq-ortvQsSsns3nUALMcnOOsRUxNluZRJ0bUb7pt4d8eMxBEC4MNB&amp;__tn__=*NK-R">#traditionnelle</a> <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/spectacle?__eep__=6&amp;__cft__[0]=AZWD5GGbC9nO_IRf6dh0hROnQq3xE5ydBPVFyH6IY2N14_9plIdwNdIXtESg_NqjSjpZWThWfPMwFkRhnnsoMs3hRLUQCOorawECdbCBk1XyfytOCvgmXg2HKRmHqO3-tuEW3cvzkwYi-y2qO_Qj0DnETM1BmjBkMtbqq-ortvQsSsns3nUALMcnOOsRUxNluZRJ0bUb7pt4d8eMxBEC4MNB&amp;__tn__=*NK-R">#spectacle</a></div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/02/431199871_1073500133721787_1561380552715286639_n.jpg',
            ],
            [
                'slug' => 'participation-au-folk2024-a-sambreville',
                'tag' => 'Actualité',
                'title' => 'Participation au Folk2024 à Sambreville',
                'date' => '2024-02-03 11:11:01',
                'resume' => 'Nous sommes ravis de vous annoncer que notre groupe de danse participera au spectacle #folk2024 de la Dapo - Danses populaires ce dimanche 4 février au centre culturel de Sambreville ! Folk est un grand spectacle de danses traditionnels organisé chaque année par notre fédération...',
                'content' => '<div>Nous sommes ravis de vous annoncer que notre groupe de danse participera au spectacle <a tabindex="0" role="link" href="https://www.facebook.com/hashtag/folk2024?__eep__=6&amp;__cft__[0]=AZUartv_b8emgS349KGAzpbyZ0F03j68cSKxprzGP7NEBA1px7fwZ1-PK6X-MWlqyuhscF9Q_JA5pDq9S_bSI3uL5egyg9mgzKAIus5-JTKXCmCZOM4moIskLm0ywuu6LigsLTZ3V-_CcH9PCAGcd_cnSjuVyO-qC0b8VVbc44NdkD0KoLvwS1c3pUZUXNW7RKWl0N9-cf08o_Q4J7mmOBYa&amp;__tn__=*NK-R">#folk2024</a> de la <a tabindex="0" role="link" href="https://www.facebook.com/DapoDanses?__cft__[0]=AZUartv_b8emgS349KGAzpbyZ0F03j68cSKxprzGP7NEBA1px7fwZ1-PK6X-MWlqyuhscF9Q_JA5pDq9S_bSI3uL5egyg9mgzKAIus5-JTKXCmCZOM4moIskLm0ywuu6LigsLTZ3V-_CcH9PCAGcd_cnSjuVyO-qC0b8VVbc44NdkD0KoLvwS1c3pUZUXNW7RKWl0N9-cf08o_Q4J7mmOBYa&amp;__tn__=-]K-R"><span>Dapo - Danses populaires</a></span> ce dimanche 4 février au centre culturel de Sambreville !</div>
<div>Folk est un grand spectacle de danses traditionnels organisé chaque année par notre fédération (cfr dapo.be)</div>
<div>&#x1f31f; Venez nombreux pour soutenir notre groupe et notre fédération.</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2024/08/424615080_1050063272732140_7469910873503745769_n.jpg',
            ],
            [
                'slug' => 'save-the-date-spectacle-de-danses-traditionnelles-30-mars-2024',
                'tag' => 'Actualité',
                'title' => 'Save the Date: Spectacle de Danses Traditionnelles 30 Mars 2024',
                'date' => '2023-10-28 15:17:47',
                'resume' => 'Chers amis et amoureux de la danse, Nous sommes ravis de vous annoncer une soirée magique de danses traditionnelles qui se déroulera le samedi 30 mars 2024 au Waux-Hall de Nivelles ! 🕺💃 Préparez vous à vivre une expérience inoubliable alors que notre groupe de danse prépare un...',
                'content' => '<p>Chers amis et amoureux de la danse,</p>
<p>Nous sommes ravis de vous annoncer une soirée magique de danses traditionnelles qui se déroulera le samedi 30 mars 2024 au Waux-Hall de Nivelles ! &#x1f57a;&#x1f483;</p>
<p>Préparez vous à vivre une expérience inoubliable alors que notre groupe de danse prépare un spectacle époustouflant qui mettra en valeur notre passion pour la danse traditionnelle.</p>
<p>Restez à l\'écoute pour plus d\'informations à venir, y compris les détails pratiques, la billetterie et bien plus encore. Nous avons hâte de partager cette soirée spéciale avec vous et de célébrer notre amour pour la danse ensemble.</p>
<p>Marquez cette date dans votre calendrier, invitez vos amis et votre famille, et rejoignez nous pour une soirée de grâce et de mouvements exquis.</p>
<p>#SaveTheDate #SpectacleDeDanse #DanseTraditionnelle #WauxHallNivelles</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/10/396187980_996285884776546_1188681568119413948_n.jpg',
            ],
            [
                'slug' => 'retour-sur-nos-seances-de-danse-traditionnelle-pendant-les-vacances-de-printemps-%f0%9f%8c%b8%f0%9f%8e%b6',
                'tag' => 'Actualité',
                'title' => 'Participation au stage communal Move and Dance pendant les vacances de printemps ! 🌸🎶',
                'date' => '2023-05-20 11:07:17',
                'resume' => 'A l\'occasion des stages multi-sport organisés par la commune de Braine-l\'Alleud, notre groupe a eu l\'opportunité d\'animer quatre séances dédiées aux enfants. Dans une optique de découverte et de mouvement, nos monitrices ont partagé leur passion du folklore avec des jeunes âgés...',
                'content' => '<p>A l\'occasion des stages multi-sport organisés par la commune de Braine-l\'Alleud, notre groupe a eu l\'opportunité d\'animer quatre séances dédiées aux enfants. Dans une optique de découverte et de mouvement, nos monitrices ont partagé leur passion du folklore avec des jeunes âgés de 4 à 14 ans.</p>
<p>Ces stages Move and Dance ont été une occasion parfaite pour initier les enfants à la beauté et à la richesse des danses traditionnelles. Pendant ces séances ludiques et énergiques, nos monitrices ont su créer une ambiance joyeuse et participative, permettant aux enfants de s\'exprimer librement à travers le mouvement.</p>
<p>Chaque jour, les enfants ont exploré des danses provenant de diverses régions du pays, découvrant ainsi la diversité culturelle et artistique de notre patrimoine.</p>
<p>Au-delà de l\'apprentissage des pas de danse, ces séances ont également favorisé le développement de la créativité, de la coordination et de l\'esprit d\'équipe chez les enfants. Ils ont appris à écouter la musique, à suivre le rythme et à interagir avec leurs camarades de danse, créant ainsi des liens.</p>
<p>Nous tenons à remercier chaleureusement la commune pour cette initiative, qui a permis à nos monitrices d\'apporter la magie de la danse folklorique à un public jeune et curieux. Nous espérons que cette expérience aura laissé une empreinte positive dans le cœur de chaque enfant, et peut-être même semé quelques graines de passion pour la danse dans leur vie future.</p>
<p>Si vous souhaitez en savoir plus sur nos activités et découvrir comment la danse traditionnelle peut enrichir la vie de vos enfants, n\'hésitez pas à nous contacter. Nous serons ravis de vous accueillir au sein de nos cours et de partager avec vous la beauté de notre héritage culturel à travers la danse</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/05/WhatsApp-Image-2023-06-03-a-11.55.572.jpg',
            ],
            [
                'slug' => '350-mercis',
                'tag' => 'Actualité',
                'title' => '350 Mercis',
                'date' => '2023-04-20 10:48:59',
                'resume' => 'Tous les membres Clap\'Sabots tiennent à vous remercier chaleureusement pour votre présence lors de notre soirée spectacle de ce samedi 15 avril ! Nous sommes d\'ores et déjà déterminés à vous offrir une expérience encore plus incroyable l\'année prochaine. Pour y parvenir, nous...',
                'content' => '<div>Tous les membres Clap\'Sabots tiennent à vous remercier chaleureusement pour votre présence lors de notre soirée spectacle de ce samedi 15 avril !</div>
<div>Nous sommes d\'ores et déjà déterminés à vous offrir une expérience encore plus incroyable l\'année prochaine.</div>
<div>Pour y parvenir, nous souhaitons connaitre votre avis :</div>
<div><a tabindex="0" role="link" href="https://docs.google.com/forms/d/e/1FAIpQLSdyi6DGlFdXk3wbLfnvS5LXl_MVCP4CrEG5GgKxsoo98IS93A/viewform?fbclid=IwAR1GgAyZbKKk0sB-SvrMvKzkw8diiC5pvoEKIxNYivqNGanUZ0LTeKGhXpo" target="_blank" rel="nofollow noopener noreferrer">https://docs.google.com/.../1FAIpQLSdyi6DGlFdXk3.../viewform</a></div>
<div>Promis, ce sondage ne vous prendra que quelques minutes.</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/06/Capture-decran-2023-06-03-105144.png',
            ],
            [
                'slug' => 'gala-2023-accessibilite',
                'tag' => 'Actualité',
                'title' => 'Gala 2023 : Accessibilité',
                'date' => '2023-04-13 18:38:45',
                'resume' => 'Cette année notre spectacle à lieu à Anderlecht. Mais pas de panique, c\'est seulement à 700m du Ring. Plusieurs parkings Facile en transport en commun 🚌Bus 73 et 75 🚇Metro 1 et5 🚆Train S3 et S8 CERIA - Auditorium Jacques Brel Av. Emile Gryson 11070 Anderlecht',
                'content' => '<div>
<div>Cette année notre spectacle à lieu à Anderlecht. Mais pas de panique, c\'est seulement à 700m du Ring. Plusieurs parkings</div>
</div>
<div>
<div>Facile en transport en commun</div>
<div>&#x1f68c;Bus 73 et 75</div>
<div>&#x1f687;Metro 1 et5</div>
<div>&#x1f686;Train S3 et S8</div>
</div>
<div>
<div>CERIA - Auditorium Jacques Brel</div>
<div>Av. Emile Gryson 11070 Anderlecht</div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/04/336374734_197064013090219_4596559058933635581_n.jpg',
            ],
            [
                'slug' => 'gala-2023-j-3-dernieres-repetitions',
                'tag' => 'Actualité',
                'title' => 'Gala 2023 : J-3 Dernières répétitions',
                'date' => '2023-04-12 18:40:29',
                'resume' => 'J-3 Dernières répétitions ➡ N\'oubliez pas de réserver vos places aux prix préventes. Les bonnes places partent très vite https://www.clapsabots.be/event/gala-annuel-de-danses-traditionnelles-46eme-anniversaire/',
                'content' => '<div>
<div>J-3 Dernières répétitions</div>
</div>
<div>
<div>&#x27a1; N\'oubliez pas de réserver vos places aux prix préventes. Les bonnes places partent très vite</div>
<div>https://www.clapsabots.be/event/gala-annuel-de-danses-traditionnelles-46eme-anniversaire/</div>
<div></div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/04/340750354_153394301004045_6568247078653607033_n.jpg',
            ],
            [
                'slug' => 'gala-2023-j-4-derniers-preparatifs-coutures',
                'tag' => 'Actualité',
                'title' => 'Gala 2023: J-4 Derniers préparatifs coutures',
                'date' => '2023-04-11 18:42:11',
                'resume' => 'J-4 On vous prépare de nouveaux costumes cousus à l\'authentique ✂️ ➡ N\'oubliez pas de réserver vos places aux prix préventes. Les bonnes places partent très vite',
                'content' => '<div>
<div>J-4 On vous prépare de nouveaux costumes cousus à l\'authentique &#x2702;&#xfe0f;</div>
</div>
<div>
<div>&#x27a1; N\'oubliez pas de réserver vos places aux prix préventes. Les bonnes places partent très vite</div>
</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2023/04/340135242_536787245275852_4659243102694611112_n.jpg',
            ],
            [
                'slug' => 'soiree-45eme-anniversaire',
                'tag' => 'Actualité',
                'title' => 'Soirée 45ème anniversaire',
                'date' => '2022-04-23 12:45:49',
                'resume' => 'C est ce soir !!!! On vous attend nombreux pour danser avec nous !',
                'content' => '<div>C est ce soir !!!!</div>
<div>On vous attend nombreux pour danser avec nous !</div>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2022/04/274580613_5474003002614447_8311260842079900986_n.jpg',
            ],
            [
                'slug' => 'rencontre-danseurs-roumains-de-lasamblul-folcloric-florile-tarcaului',
                'tag' => 'Rencontre',
                'title' => 'Rencontre avec les danseurs roumains de l\'Asamblul Folcloric Florile Tarcăului',
                'date' => '2017-09-03 11:46:56',
                'resume' => 'Le week-end du 2 et 3 septembre 2017, nous avons eu la chance d\'accueillir l\'Ensemble de danses folkloriques Florile Tarcăului. Les enfants de nos deux groupes ont pu partager des danses de chez nous et de Roumanie lors de notre cours au Stade Gaston Reiff. Nous nous sommes...',
                'content' => '<p>Le week-end du 2 et 3 septembre 2017, nous avons eu la chance d\'accueillir l\'Ensemble de danses folkloriques <strong>Florile Tarcăului.</strong></p>
<p>Les enfants de nos deux groupes ont pu partager des <strong>danses de chez nous et de Roumanie</strong> lors de notre cours au Stade Gaston Reiff.</p>
<p>Nous nous sommes retrouvés le lendemain pour une<strong> prestation sur le podium</strong> lors de la braderie de Braine-l\'Alleud</p>
<p>Très belle rencontre !<br>
Merci à tous les danseurs.</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2017/09/21442283_1487665437991229_2080004480_n.jpg',
            ],
            [
                'slug' => 'decouvrez-gala-lemission-dbranche-tvcom',
                'tag' => 'Spectacle',
                'title' => 'Découvrez notre Gala dans l\'émission dBranché sur Tvcom',
                'date' => '2017-03-07 20:27:54',
                'resume' => 'Ce samedi 25 février se déroulait notre Gala à l\'occasion de notre 40e anniversaire au Centre culturel d\'Ottignies Antal et Pablo de l\'émission dBranché nous ont suivi pendant toute la soirée. Les dbranchés vous font découvrir cette semaine le gala de danses traditionnelles de...',
                'content' => 'Ce <strong>samedi 25 février </strong>se déroulait notre Gala à l\'occasion de notre 40e anniversaire au Centre culturel d\'Ottignies

Antal et Pablo de l\'émission dBranché nous ont suivi pendant toute la soirée.
<blockquote>Les dbranchés vous font découvrir cette semaine le gala de danses traditionnelles de l\'Ensemble Clap\'Sabots. Un spectacle qui vous fera voyager à travers de nombreux pays, la danse un bon moyen de découvrir les coutumes locales.</blockquote>
<a href="http://www.tvcom.be/video/culture/dbranche-13-21-danses-traditionnelles-_19470_297.html"><strong>Redécouvrir le reportage</strong> </a>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2017/05/IMG_4912.jpg',
            ],
            [
                'slug' => 'folk-2016-foyer-culturel-de-saint-ghislain',
                'tag' => 'Spectacle',
                'title' => 'Folk 2016 au Foyer culturel de Saint-Ghislain',
                'date' => '2016-11-20 22:32:49',
                'resume' => 'Ce dimanche 20 novembre se déroulait le spectacle annuel organisé par la Fédération Wallonne des Groupements de Danses et Musiques Populaires (DAPO), à savoir FOLK 2016. C\'est pas moins de 20 groupes de danses traditionnelles qui se sont enchaînés sur la scène du  Foyer culturel...',
                'content' => '<p>Ce <strong>dimanche 20 novembre</strong> se déroulait le spectacle annuel organisé par la Fédération Wallonne des Groupements de Danses et Musiques Populaires (DAPO), à savoir FOLK 2016.</p>
<p>C\'est pas moins de 20 groupes de danses traditionnelles qui se sont enchaînés sur la scène du  <strong>Foyer culturel de Saint-Ghislain </strong>pour faire  vivre des moments intenses de folklore de différents pays et, bien entendu, de notre Wallonie.</p>
<p>Notre section Jeunes y a presté une suite de danses d\'Argentine alors que notre section Israël une suite de danses Hassidiques.</p>
<p>&nbsp;</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2016/11/IMG_9566.jpg',
            ],
            [
                'slug' => 'lensemble-clapsabots-a-bruxelles-champetre-2016',
                'tag' => 'Animation',
                'title' => 'L’Ensemble Clap’Sabots à Bruxelles Champêtre 2016',
                'date' => '2016-09-24 20:49:18',
                'resume' => 'Le dimanche 18 septembre 2016, se déroulait la journée sans voiture. Au cours de cette journée, diverses activités avaient lieu. L’Ensemble Clap’Sabots quant à lui avait été invité pour la deuxième fois par la province du Brabant Wallon pour participer à la journée Bruxelles...',
                'content' => '<p>Le dimanche 18 septembre 2016, se déroulait la journée sans voiture.</p>
<p>Au cours de cette journée, diverses activités avaient lieu. L’Ensemble Clap’Sabots quant à lui avait été invité pour la deuxième fois par la province du Brabant Wallon pour participer à la journée Bruxelles Champêtre qui avait lieu sur les pavés devant le palais Royal et le parc Royal.</p>
<p>Cette journée s’est déroulée sous un soleil resplendissant, avec un public plus que nombreux et heureux de tout ce qu’il y avait comme animations.</p>
<p>L’Ensemble Clap’Sabots a pu montrer toute l’étendue de ses capacités en présentant tous les groupes qui composent l’ensemble, des plus jeunes au plus anciens. Les danses présentées furent diverses et variées tant au point de vue rythmique qu’au niveau du voyage au travers des cultures (Italie, Hongrie, Argentine, Roumanie, ....). Le groupe a par ailleurs animé les passants durant toutes la journée avec des danses simples et abordables</p>
<p>Toute la troupe de danseurs est ravie d’avoir pu participer à une journée comme celle-là, où nature, ambiance, danse et famille étaient mêlées.</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2016/11/IMG_9182.jpg',
            ],
            [
                'slug' => '71eme-braderie-de-braine-lalleud',
                'tag' => 'Spectacle',
                'title' => '71ème braderie de braine-l\'Alleud',
                'date' => '2016-09-02 16:11:00',
                'resume' => 'Danses Argentines par le groupe des Jeunes ce WE à la 71ème Braderie traditionnelle de Braine l\'Alleud.',
                'content' => '<p>Danses Argentines par le groupe des Jeunes ce WE à la <a href="https://www.facebook.com/events/297795893910951/?ref=3&amp;ref_newsfeed_story_type=regular&amp;action_history=null&amp;source=3&amp;source_newsfeed_story_type=regular" data-hovercard="/ajax/hovercard/event.php?id=297795893910951&amp;extragetparams=%7B%22source%22%3A3%2C%22source_newsfeed_story_type%22%3A%22regular%22%2C%22action_history%22%3A%22null%22%7D">71ème Braderie traditionnelle de Braine l\'Alleud.</a></p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2016/10/14206129_1350956324919156_130669021971957056_o.jpg',
            ],
            [
                'slug' => 'souper-spectacle-le-samedi-5mars',
                'tag' => 'Spectacle',
                'title' => 'Souper-Spectacle le Samedi 5mars',
                'date' => '2016-01-15 08:08:37',
                'resume' => 'Voici l\'évènement annuel des Clap\'Sabots. Cette année à la place d\'un gala nous vous avons préparé une soirée spectacle avec souper. L\'année prochaine nous reviendront à la formule gala pour les 40 ans d’existence du groupe.',
                'content' => '<p>Voici l\'évènement annuel des Clap\'Sabots. Cette année à la place d\'un gala nous vous avons préparé une soirée spectacle avec souper. L\'année prochaine nous reviendront à la formule gala pour les 40 ans d’existence du groupe.</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2016/01/12508775_1182981478383309_7280203739246645536_n.jpg',
            ],
            [
                'slug' => 'gala-de-danses-traditionnelles-par-lensemble-clapsabots-38eme-annee',
                'tag' => 'Spectacle',
                'title' => 'Gala de danses traditionnelles par l\'Ensemble Clap\'Sabots - 38ème année',
                'date' => '2015-01-16 08:08:03',
                'resume' => 'Le Samedi 21 Mars 2015, l\'Ensemble Clap\'Sabots vous propose son Gala annuel de danses traditionnelles. Pour sa 38ème année, ce spectacle haut en couleur se déroulera au Waux Hall de Nivelles. Chaque année, nous proposons un gala plus important que nos autres démonstrations....',
                'content' => '<p>Le Samedi <strong>21 Mars 2015</strong>, l\'<strong>Ensemble Clap\'Sabots</strong> vous propose son <strong>Gala annuel de danses traditionnelles</strong>.<br>
Pour sa 38ème année, ce spectacle haut en couleur se déroulera au <strong>Waux Hall de Nivelles.</strong></p>
<p>Chaque année, nous proposons un gala plus important que nos autres démonstrations. C\'est l\'occasion pour nous de vous montrer les suites traditionnelles de différents pays, apprises durant une année. Les nouveaux costumes créés et cousus à l’authentique par nos danseurs, brilleront sous les projecteurs. Durant ce spectacle vous pourrez apprécier nos différents groupes, des plus jeunes aux plus chevronnés.</p>
<p>Ouverture des portes : 19h30<br>
Spectacle : 20h</p>
<p>Tarifs :<br>
Adultes : 15€ (13€ en prévente)<br>
Enfants/Etudiants: 12€ (10€ en prévente)</p>
<p>Renseignements &amp; réservations<br>
Par téléphone : 02/384.09.02 (Lu-Ve de 9h à 18h)<br>
Par mail : contact@clapsabots.be<br>
Site web : <a href="http://www.clapsabots.be/" target="_blank" rel="nofollow nofollow">www.clapsabots.be</a></p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2015/01/Affiche-Gala-2015.jpg',
            ],
            [
                'slug' => 'braderie-de-braine-lalleud',
                'tag' => 'Spectacle',
                'title' => 'Braderie de Braine-l\'Alleud',
                'date' => '2014-09-02 08:07:36',
                'resume' => 'Comme l\'an passé, l\'Ensemble Clap\'Sabots sera une nouvelle fois présent sur la braderie de Braine-l\'Alleud le samedi 6 et le dimanche 7 septembre. Nous dansons Samedi et Dimanche à 14h et 16h sur la place du Môle. N\'hésitez pas à venir à notre stand pour nous donner un coup de...',
                'content' => '<p>Comme l\'an passé, l\'Ensemble Clap\'Sabots sera une nouvelle fois présent sur la <strong>braderie de Braine-l\'Alleud</strong> le <strong>samedi 6 et le dimanche 7 septembre</strong>.<br>
Nous dansons Samedi et Dimanche à <strong>14h et 16h sur la place du Môle</strong>.<br>
N\'hésitez pas à venir à notre stand pour nous donner un coup de main, danser un pas de polka, ou simplement nous faire un petit coucou.</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2014/09/11889478_1109292505752207_648687334082622130_n.jpg',
            ],
            [
                'slug' => 'gala-de-danses-traditionnelles-par-lensemble-clapsabots-37eme-annee',
                'tag' => 'Spectacle',
                'title' => 'Gala de danses traditionnelles par l\'Ensemble Clap\'Sabots - 37ème année',
                'date' => '2013-12-07 08:06:18',
                'resume' => 'Le Samedi 1 Mars 2014, l\'Ensemble Clap\'Sabotsvous propose son Gala annuel de danses traditionnelles. Pour sa 37ème année, ce spectacle haut en couleur se déroulera au Centre Culturel d\'Ottignies. Chaque année, nous proposons un gala plus important que nos autres démonstrations....',
                'content' => '<p><img id="alttext-image" width="16" height="16" align="left" /></p>
<p>Le <strong>Samedi 1 Mars 2014</strong>, l\'<strong>Ensemble Clap\'Sabots</strong>vous propose son Gala annuel de danses traditionnelles.<br>
Pour sa 37ème année, ce spectacle haut en couleur se déroulera au <strong>Centre Culturel d\'Ottignies.</strong></p>
<p>Chaque année, nous proposons un gala plus important que nos autres démonstrations. C\'est l\'occasion pour nous de vous montrer les suites traditionnelles de différents pays, <strong>apprises durant une année</strong>. Les nouveaux <strong>costumes</strong> créés et cousus à l’authentique par nos danseurs, brilleront sous les projecteurs. Durant ce spectacle vous pourrez apprécier nos <strong>différents groupes</strong>, des plus jeunes aux plus chevronnés.</p>
<p>Ouverture des portes : 19h30<br>
Spectacle : 20h</p>
<p><strong>Tarifs : </strong><br>
Adultes : 15€ (13€ en prévente)<br>
Enfants/Etudiants: 12€ (10€ en prévente)</p>
<p><strong>Renseignements &amp; réservations</strong><br>
Par téléphone : 02/384.09.02 ou 0477/27.32.12<br>
Par mail : contact@clapsabots.be<br>
Site web : <a href="http://www.clapsabots.be/" target="_blank" rel="nofollow">www.clapsabots.be</a></p>
<p><a href="http://www.poleculturel.be/images/lieux/plan_centre_culturel2.jpg">Plan d\'accès</a></p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2013/12/IMG_2862.jpg',
            ],
            [
                'slug' => 'fete-de-la-saint-jean-a-lillois',
                'tag' => 'Spectacle',
                'title' => 'Fête de la Saint-Jean à Lillois',
                'date' => '2013-06-12 08:05:39',
                'resume' => 'Prestation de danses de Roumanie, Mexique, Chine et Irlande à 16h30 aux Feux de la Saint-Jean de Lillois, le dimanche... Le Tour de la Saint-Jean existe depuis plus de 800 ans à Lillois-Witerzée. Tout se passe dans le site magnifique de la Ferme del Tour, de la Chapelle de...',
                'content' => '<p>Prestation de danses de Roumanie, Mexique, Chine et Irlande à <strong>16h30 aux Feux de la Saint-Jean de Lillois</strong>, le dimanche...<br>
Le Tour de la Saint-Jean existe depuis plus de 800 ans à Lillois-Witerzée. Tout se passe dans le site magnifique de la Ferme del Tour, de la Chapelle de Witterzée, sous chapiteau. Le samedi, sont au programme : une brocante, une exposition et un cortège de tracteurs anciens, le tout suivi par une soirée barbecue. Le dimanche, place au Tour à proprement parlé, avec chars, fanfare, cavaliers et édiles communaux à 14h30. Vers 16h30, place à un goûter champêtre suivi par, à 20h30, le départ des chars conduisant la sorcière au bûcher, des danses autour du feu et une soirée.</p>',
                'image' => 'https://www.clapsabots.be/wp-content/uploads/2013/06/2013-06-30-16.40.56.jpg',
            ],
            [
                'slug' => 'gala-de-danses-traditionnelles-par-lensemble-clapsabots-avec-la-participation-de-lensemble-pas-dla-yau',
                'tag' => 'Actualité',
                'title' => 'Gala de danses traditionnelles par l\'Ensemble Clap\'Sabots avec la participation de l\'Ensemble Pas d\'La Yau',
                'date' => '2013-02-15 08:05:07',
                'resume' => 'Le Samedi 27 avril 2013, l\'Ensemble Clap\'Sabots vous propose son Gala annuel de danses traditionnelles. Pour sa 36ème année, ce spectacle haut en couleur se déroulera au Centre Culturel d\'Ottignies avec la participation de l\'Ensemble folklorique Pas D\'La Yau Chaque année, nous...',
                'content' => '<img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc6/277014_369289876511591_1661795479_n.jpg" alt="" width="180" height="254" />Le <strong>Samedi 27 avril 2013</strong>, l\'<strong>Ensemble Clap\'Sabots</strong> vous propose son Gala annuel de danses traditionnelles.
Pour sa 36ème année, ce spectacle haut en couleur se déroulera au <strong>Centre Culturel d\'Ottignies </strong>avec la participation de l\'<strong><a href="http://www.pasdlayau.be/">Ensemble folklorique Pas D\'La Yau</a></strong>
<p>Chaque année, nous proposons un gala plus important que nos autres démonstrations. C\'est l\'occasion pour nous de vous montrer les suites traditionnelles de différents pays, <strong>apprises durant une année</strong>. Les nouveaux <strong>costumes</strong> créés et cousus à l’authentique par nos danseurs, brilleront sous les projecteurs. Durant ce spectacle vous pourrez apprécier nos <strong>différents groupes</strong>, des plus jeunes aux plus chevronnés.</p>
<p>Au cours du mois de septembre 2012, nos différentes sections ont pu profiter de deux semaines de  stage. Le chorégraphe roumain Marius Ursu nous a enseigné des danses des<strong>différentes régions de Roumanie</strong> (Transylvanie, Făgăraș, Moldavie, …), vous pourrez apercevoir celles-ci au cours de notre spectacle.</p>
<p>Nouveautés pour ce 36<sup>e</sup> anniversaire, le Mexique et l’Irlande feront leur apparition dans le spectacle ainsi que la Chine. La collaboration entre la Commune de Braine-l’Alleud et le Centre Culturel EU-Chine nous a en effet permis de participer à un stage de Yangko<strong>, danse traditionnelle chinoise</strong>.</p>
<p>Pour cette année 2013, l’Ensemble Clap’Sabots invite également l’Ensemble Folklorique Pas D’La Yau, lequel nous fera l’honneur de nous présenter des danses de la Renaissance, des danses flamandes et une danse d’épées.</p>
Ouverture des portes : 19h30
Spectacle : 20h

<strong>Tarifs : </strong>
Adultes : 15€ (13€ en prévente)
Enfants: 12€ (10€ en prévente)

<strong>Renseignements &amp; réservations</strong>
Par téléphone : 02/384.09.02 ou 0477/27.32.12
Par mail : contact@clapsabots.be
Site web : <a href="http://www.clapsabots.be/" target="_blank" rel="nofollow">www.clapsabots.be</a>

<a href="http://www.poleculturel.be/images/lieux/plan_centre_culturel2.jpg">Plan d\'accès</a>',
                'image' => 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc6/277014_369289876511591_1661795479_n.jpg',
            ],
            [
                'slug' => 'les-clapsabots-se-mobilisent-contre-le-cancer-2',
                'tag' => 'Actualité',
                'title' => 'Les Clap\'Sabots se mobilisent contre le cancer',
                'date' => '2013-01-02 08:04:33',
                'resume' => 'Le 18 et 19 mai prochain auront lieu pour la troisième fois à Brainel\'Alleud, le Relais pour la vie. Relais pour la Vie est un événement communautaire où l\'on célèbre le courage des personnes atteintes de cancer, où l\'on rend hommage à ceux que le cancer a emporté, et lors...',
                'content' => 'Le 18 et 19 mai prochain auront lieu pour la troisième fois à Brainel\'Alleud, le Relais pour la vie.

<em><a href="http://www.relaispourlavie.be/evenement/origines">Relais pour la Vie</a> est un événement communautaire où l\'on célèbre le courage des personnes atteintes de cancer, où l\'on rend hommage à ceux que le cancer a emporté, et lors duquel on lutte ensemble contre la maladie.</em>

Les Clap\'Sabots se mobilisent pour relever 2 défis:
<div>
<ul>
 	<li>ne pas interrompre le Relais qui se déroulera autour du stade Gaston Reiff durant 24heures</li>
 	<li>récolter des fonds pour la recherche contre le cancer, en proposant diverses animations,. L\'objectif de notre équipe est d\'atteindre les 1000 euros.</li>
</ul>
</div>
<div>Rejoignez-nous, nous avons besoin de vous.</div>
<div><a href="http://www.relaispourlavie.be/teams/ensemble-clapsabots">Clique ici si tu souhaite rejoindre notre équipe</a></div>
<div>Infos sur le site <a href="http://www.relaispourlavie.be/" target="_blank">www.relaispourlavie.be</a> de Braine-l\'Alleud.</div>',
                'image' => null,
            ],
            [
                'slug' => 'lensemble-clapsabots-vous-souhaite-de-tres-bonne-fetes-de-fin-dannee',
                'tag' => 'Actualité',
                'title' => 'L\'Ensemble Clap\'Sabots vous souhaite de très bonne fêtes de fin d\'année',
                'date' => '2012-12-24 08:04:01',
                'resume' => 'Qu\'elle soit pleine de réussite scolaire et professionnelle, pleine de danses et de chorégraphies qui vous comblent. Qu\'elle soit, toute entière, faite d\'amitié.24',
                'content' => '<p>Qu\'elle soit pleine de réussite scolaire et professionnelle, pleine de danses et de chorégraphies qui vous comblent.<br>
Qu\'elle soit, toute entière, faite d\'amitié.24</p>',
                'image' => null,
            ],
            [
                'slug' => 'braderie-de-braine-lalleud-2012',
                'tag' => 'Actualité',
                'title' => 'Braderie de Braine l\'Alleud 2012',
                'date' => '2012-09-09 08:03:26',
                'resume' => 'La promotion du groupe fut un succès ce week-end !!! Plusieurs nouveaux danseurs (et danseuses!) risquent de venir grossir les rangs de chacun des groupes. De nombreuses personnes intéresse d\'être au courant de l’actualité du groupe. Sous un soleil de plomb, la bonne humeur...',
                'content' => '<p>La promotion du groupe fut un succès ce week-end !!! Plusieurs nouveaux danseurs (et danseuses!) risquent de venir grossir les rangs de chacun des groupes. De nombreuses personnes intéresse<br>
d\'être au courant de l’actualité du groupe.<br>
Sous un soleil de plomb, la bonne humeur était de rigueur. Quelques boissons bien fraîches furent de rigueur. :=)<br>
Merci à tous les participants qui ont donné de leur temps et de leur énergie pour mener à bien cet évènement!</p>',
                'image' => null,
            ],
            [
                'slug' => 'nos-geants-antoine-et-leonie-toujours-bien-vivants',
                'tag' => 'Actualité',
                'title' => 'Nos géants (Antoine et Léonie) toujours bien vivants',
                'date' => '2012-08-02 08:02:48',
                'resume' => 'Le journal L’Avenir vient de publier dans la page Brabant Wallon du journal ce jeudi , un article sur “Antoine et Léonie” nos géants. Ceux-ci ont été créés pour le Mondial des cultures car les Québécois ne connaissaient pas ce qu’ils appellent « une grande poupée » Voir l\'article',
                'content' => '<div>

<img src="http://1.lavenircdn.net/Assets/Images_Upload/Actu24/2012/08/02/PID_$1485300$_ebf83f30-db1a-11e1-98ae-5ba8774686fc_original.jpg.h170.jpg" alt="" width="217" height="170" />

Le journal <strong>L’Avenir</strong> vient de publier dans la page Brabant Wallon du journal ce jeudi , un article sur “<strong>Antoine et Léonie</strong>”

nos géants.

Ceux-ci ont été créés pour le <strong>Mondial des cultures</strong> car les Québécois ne connaissaient pas ce qu’ils appellent « une grande poupée »

</div>
<div></div>
<div>

<a href="http://www.lavenir.net/article/detail.aspx?articleid=DMF20120802_00188062" target="_blank">Voir l\'article</a>

</div>',
                'image' => 'http://1.lavenircdn.net/Assets/Images_Upload/Actu24/2012/08/02/PID_$1485300$_ebf83f30-db1a-11e1-98ae-5ba8774686fc_original.jpg.h170.jpg',
            ],
            [
                'slug' => 'la-chine-a-braine-lalleud',
                'tag' => 'Actualité',
                'title' => 'La Chine à Braine-l\'Alleud',
                'date' => '2012-07-10 08:02:13',
                'resume' => 'Dans le cadre d’une collaboration entre laCommune de Braine-l’Alleud et le Centre Culturel EU-Chine dirigé par Mme Gao, 2 danseuses chinoises sont venues donner un stage de Yangko, danse traditionnelle chinoise, auxClap\'Sabots ainsi q\'aux élèves de l’école de danse de l’Académie...',
                'content' => '<p><img id="alttext-image" width="16" height="16" align="left" /></p>
<p>Dans le cadre d’une collaboration entre la<strong>Commune de Braine-l’Alleud et le Centre Culturel EU-Chine</strong> dirigé par Mme Gao, 2 danseuses chinoises sont venues donner un <strong>stage de Yangko</strong>, danse traditionnelle chinoise, aux<strong>Clap</strong><strong>\'Sabots</strong> ainsi q\'aux élèves de l’école de danse de l’Académie de Musique de Braine-l’Alleud.</p>
<p>Ce stage qui s’est déroulé pendant la dernière semaine du mois de juin au Centre Sportif Gaston Reiff a été répercuté dans de nombreux medias chinois</p>',
                'image' => null,
            ],
            [
                'slug' => 'fete-de-la-st-jean-a-lillois-witterzee-le-we-du-23-et-24-juin-2012',
                'tag' => 'Actualité',
                'title' => 'Fête de la St Jean à Lillois-Witterzée le we du 23 et 24 Juin 2012',
                'date' => '2012-06-22 08:01:34',
                'resume' => '22Le W.E du 23 et 24 Juin , le comité de la St Jeanvous invite à venir faire la fête à Lillois. Le samedi : Brocante (réservation), repas (réservation) suivi d’une soirée dansante. Le dimanche : Messe à la chapelle St Martin, bbq, Tour de la St Jean en tracteur avec les enfants...',
                'content' => '<p>22Le W.E du 23 et 24 Juin , le comité de la<strong> St Jean</strong>vous invite à venir faire la <strong>fête à Lillois</strong>.<br>
Le samedi : Brocante (réservation), repas (réservation) suivi d’une soirée dansante.<br>
Le dimanche : Messe à la chapelle St Martin, bbq, Tour de la St Jean en tracteur avec les enfants costumés sur les chars, goûter, animations diverses, brûlage de la sorcière.</p>
<p><strong>Prestation des Jeunes le samedi et des Adultes le dimanche.</strong></p>
<p>Animations foraines pendant 3 jours.</p>
<p>LIEU : Dans et autour du chapiteau situé au coin de la rue Fontaine St Martin et de la rue Ramelot.<br>
CONTACT : tel : 04.8 ou 0474/575.449</p>',
                'image' => null,
            ],
            [
                'slug' => 'gourmandises-et-couleurs-ce-samedi-3-juin-a-villers-la-ville',
                'tag' => 'Actualité',
                'title' => 'Gourmandises et Couleurs ce Samedi 3 juin à Villers-la-Ville',
                'date' => '2012-05-30 08:00:55',
                'resume' => 'Toutes les saveurs et productions savoureuses que compte le Brabant wallon seront réuniesce dimanche 3 juin à l\'Abbaye de Villers. Redécouvrez le goût du lait frais, les vertus des légumes oubliés, la saveur d\'un bon chocolat, les tartes les plus savoureuses et les breuvages les...',
                'content' => '<p>Toutes les saveurs et productions savoureuses que compte le <strong>Brabant wallon</strong> seront réunies<strong>ce dimanche 3 juin</strong> à l\'<strong>Abbaye de Villers</strong>. Redécouvrez le goût du lait frais, les vertus des légumes oubliés, la saveur d\'un bon chocolat, les tartes les plus savoureuses et les breuvages les plus doux.</p>
<p>A cette occassion, l\'<strong>Ensemble Clap\'Sabots</strong> se produira vers 16h et 17h30. C\'est un ensemble de <strong>danses </strong>du <strong>folklore Belge</strong> que nous vous présentrons.</p>
<p>Nous vous y attendons nombreux.</p>
<p><strong>Entrée Gratuite</strong></p>
<p>Plus d\'informations : <a href="http://www.gourmandisebw.be/">http://www.gourmandisebw.be/</a></p>',
                'image' => null,
            ],
            [
                'slug' => 'les-clapsabots-se-mobilisent-contre-le-cancer',
                'tag' => 'Actualité',
                'title' => 'Les Clap\'Sabots se mobilisent contre le cancer',
                'date' => '2012-05-14 07:59:58',
                'resume' => 'Le 26 et 27 mai proc14hain les Clap\'Sabots se mobilisent pour relever 2 défis: ne pas interrompre le Relais qui se déroulera autour du stade Gaston Reiff durant 24heures récolter des fonds pour la recherche contre le cancer, en proposant diverses animations,. L\'objectif de notre...',
                'content' => '<img id="alttext-image" width="16" height="16" align="left" />

Le 26 et 27 mai proc14hain les Clap\'Sabots se mobilisent pour relever 2 défis:
<div>
<ul>
 	<li>ne pas interrompre le Relais qui se déroulera autour du stade Gaston Reiff durant 24heures</li>
 	<li>récolter des fonds pour la recherche contre le cancer, en proposant diverses animations,. L\'objectif de notre équipe est d\'atteindre les 1000 euros.</li>
</ul>
</div>
<div>Rejoignez-nous, nous avons besoin de vous. <a href="http://www.relaispourlavie.be/teams/lensemble-clapsabots-et-la-bouba-team">Clique ici si tu souhaite </a><a href="http://www.relaispourlavie.be/teams/lensemble-clapsabots-et-la-bouba-team">r</a>ejoindre notre équipe

</div>
<div>Infos sur le site <a href="http://www.relaispourlavie.be/" target="_blank">www.relaispourlavie.be</a> de Braine-l\'Alleud.</div>',
                'image' => null,
            ],
            [
                'slug' => 'gala-de-danses-traditionnelles-par-lensemble-clapsabots-35eme',
                'tag' => 'Actualité',
                'title' => 'Gala de danses traditionnelles par l\'Ensemble Clap\'Sabots - 35ème',
                'date' => '2011-12-17 07:59:21',
                'resume' => 'Le Samedi 3 mars 2012, l\'Ensemble Clap\'Sabots vous propose son Gala annuel de danses traditionnelles. Pour son 35ème anniversaire, ce spectacle haut en couleur se déroulera au Centre Culturel d\'Ottignies Ouverture des portes : 19h30 Spectacle : 20h Tarifs : Adultes : 15€ (13€ en...',
                'content' => '<div>

Le <strong>Samedi 3 mars 2012</strong>, l\'<strong>Ensemble Clap\'Sabots</strong> vous propose son Gala annuel de danses traditionnelles.
Pour son 35ème anniversaire, ce spectacle haut en couleur se déroulera au <strong>Centre Culturel d\'Ottignies</strong>

Ouverture des portes : 19h30
Spectacle : 20h

<strong>Tarifs : </strong>
Adultes : 15€ (13€ en prévente)
Enfants: 12€ (10€ en prévente)

<strong>Renseignements &amp; réservations</strong>
Par téléphone : 02/384.09.02 ou 0477/27.32.12
Par mail : contact@clapsabots.be
Site web : <a href="http://www.clapsabots.be/" target="_blank" rel="nofollow">www.clapsabots.be</a>

<a href="http://www.poleculturel.be/images/lieux/plan_centre_culturel2.jpg">Plan d\'accès</a>

</div>
<img src="https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-ash4/s720x720/421390_379041765443955_186989874649146_1645374_2119733947_n.jpg" alt="" width="720" height="405" />',
                'image' => 'https://fbcdn-sphotos-a.akamaihd.net/hphotos-ak-ash4/s720x720/421390_379041765443955_186989874649146_1645374_2119733947_n.jpg',
            ],
            [
                'slug' => 'folk-2011-a-vise',
                'tag' => 'Actualité',
                'title' => 'Folk 2011 à Visé',
                'date' => '2011-11-01 07:58:47',
                'resume' => 'Le dimanche 27 novembre, à partir de 14h, au Hall omnisports de Visé, plusieurs groupes de danses traditionnelles vous feront vivre des moments intenses de folklore de différents pays et, bien entendu, de notre Wallonie également. Folk 2011 est une organisation de la Fédération...',
                'content' => '<p>Le <strong>dimanche 27 novembre</strong>, à partir de 14h, au <strong>Hall omnisports de Visé</strong>, plusieurs groupes de danses traditionnelles vous feront vivre des moments intenses de folklore de différents pays et, bien entendu, de notre Wallonie également.</p>
<p><strong>Folk 2011</strong> est une organisation de la Fédération Wallonne des Groupements de Danses et Musiques Populaires (DAPO).</p>
<p>L\'<strong>Ensemble Clap\'Sabots</strong> y prestera deux suites de danses des pays de l\'Est.</p>
<p>Ce spectacle ouvert à tous a lieu pour la première fois en Basse-Meuse</p>
<p><strong>PAF</strong>: 8€, seniors et membres DAPO: 5€, gratuit pour les moins de 15 ans<br>
<strong>Renseignements</strong>: 04/370.04.55</p>
<p><strong>Réservation Echevinat de la Culture</strong> : 04/374.85.50</p>',
                'image' => null,
            ],
            [
                'slug' => 'gala-2012-au-centre-culturel-d-ottignies-le-samedi-3-mars-a-20h',
                'tag' => 'Actualité',
                'title' => 'Gala 2012 au Centre culturel d Ottignies le samedi 3 mars à 20h',
                'date' => '2011-09-28 07:58:20',
                'resume' => 'C\'est maintenant officiel, l\'Ensemble Clap\'Sabots présentera son spectacle de Gala 2012 au Centre culturel d\'Ottignies le samedi 3 mars à 20h. Réservation à partir de Janvier. Bloquez déjà la date dans votre agenda. :=)',
                'content' => '<p>C\'est maintenant officiel, l\'Ensemble Clap\'Sabots présentera son spectacle de Gala 2012 au Centre culturel d\'Ottignies le samedi 3 mars à 20h. Réservation à partir de Janvier. Bloquez déjà la date dans votre agenda. :=)</p>',
                'image' => null,
            ],
            [
                'slug' => 'anniversaire-les-25-ans-du-hall-omnisports-gaston-reiff',
                'tag' => 'Actualité',
                'title' => 'Anniversaire : les 25 ans du hall omnisports Gaston Reiff',
                'date' => '2011-09-04 07:57:45',
                'resume' => 'Ce vendredi 9 septembre, le stade Gaston Reiff fête ses 25 ans. Pour cet évenement, l\'Ensemble Clap\'Sabots tiendra un stand. N\'hésitez pas à venir nous faire un petit coucou. Plus d\'informations sur le site de la commune. Notre salle de répétiton ne sera pas disponible. Les...',
                'content' => '<p>Ce vendredi <strong>9 septembre</strong>, le <strong>stade Gaston Reiff fête ses 25 ans</strong>.</p>
<p>Pour cet évenement, l\'<strong>Ensemble Clap\'Sabots</strong> tiendra un stand. N\'hésitez pas à venir nous faire un petit coucou.</p>
<p>Plus d\'informations sur le site de la <a href="http://www.braine-lalleud.be/fr/anniversaire-les-25-ans-du-hall-omnisports.html?cmp_id=24&amp;news_id=2311&amp;vID=226">commune</a>.</p>
<p>Notre salle de répétiton ne sera pas disponible. Les cours de danses aurons donc exceptionnellement lieu ailleurs.</p>',
                'image' => null,
            ],
            [
                'slug' => '66e-braderie-des-commercants-de-braine-lalleud',
                'tag' => 'Actualité',
                'title' => '66e Braderie des commerçants de Braine-l\'Alleud',
                'date' => '2011-08-30 07:56:57',
                'resume' => 'N’hésitez pas à venir nous donner un petit bonjour à notre stand ce samedi et dimanche à la braderie de Braine-l\'Alleud. Photos et vidéos de nos derniers spectacles y seront présents.',
                'content' => '<p><img src="https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/277030_167891646618217_1387311_s.jpg" alt="" width="100" height="145" />N’hésitez pas à venir nous donner un petit bonjour à notre stand ce samedi et dimanche à la braderie de Braine-l\'Alleud.</p>
<p>Photos et vidéos de nos derniers spectacles y seront présents.</p>',
                'image' => 'https://fbcdn-profile-a.akamaihd.net/hprofile-ak-snc4/277030_167891646618217_1387311_s.jpg',
            ],
            [
                'slug' => 'le-relais-pour-la-vie',
                'tag' => 'Actualité',
                'title' => 'Le Relais pour la Vie',
                'date' => '2011-08-26 07:56:26',
                'resume' => 'Le Relais pour la Vie est un événement solidaire destiné à lever des fonds pour la lutte contre le cancer. Importé des États-Unis, il est organisé pour la 1ère fois en Belgique. Et c’est à Braine-l’Alleud qu’il se déroulera les 27 et 28 août prochain. Pour soutenir l\'événement,...',
                'content' => '<p><strong>Le Relais pour la Vie</strong> est un <strong>événement solidaire</strong> destiné à lever des fonds pour la <strong>lutte contre le cancer</strong>. Importé des États-Unis, il est organisé pour la 1ère fois en Belgique. Et c’est à Braine-l’Alleud qu’il se déroulera les 27 et 28 août prochain.</p>
<p>Pour soutenir l\'événement, les <strong>Clap\'Sabots</strong> donnerons un petit <strong>spectacle de danses wallonnes</strong> vers 20h ce samedi 27 au <strong>stade Gaston Reiff</strong></p>',
                'image' => null,
            ],
            [
                'slug' => 'danses-folkloriques-au-parc-meudon-a-bruxelles',
                'tag' => 'Actualité',
                'title' => 'Danses folkloriques au parc Meudon a Bruxelles',
                'date' => '2011-08-21 07:55:36',
                'resume' => 'A l\'initiative de Claudine, pendant les mois de juillet et d\'août, TOUS LES VENDREDIS, au Parc Georges Henri, Square Meudon à Bruxelles, danses folkloriques internationales. Animation pour tous de 19 à 21h. avec Jules, Robert, René, Brigitte, Danielle et bien d\'autres avec Dany...',
                'content' => '<p>A l\'initiative de Claudine, pendant les mois de juillet et d\'août, TOUS LES VENDREDIS, <strong>au Parc Georges Henri,</strong> Square Meudon à Bruxelles, danses folkloriques internationales.<br>
<strong>Animation pour tous de 19 à 21h</strong>. avec Jules, Robert, René, Brigitte, Danielle et bien d\'autres avec Dany à la sono, toujours au poste.</p>
<p>Cette année, pour les 15 ans de l\'animation au Parc Georges-Henri, <strong>chaque vendredi</strong> un groupe a accepté de venir faire <strong>un petit spectacle</strong> en costume (si possible) et vous apprendre 1 ou 2 danses de son répertoire. Ce vendredi, ce sera le groupe "<strong>Clap\'Sabots</strong>" qui sera à l\'honneur.2121</p>',
                'image' => null,
            ],
            [
                'slug' => 'festival-macadanse',
                'tag' => 'Actualité',
                'title' => 'Festival MacaDanse',
                'date' => '2011-06-26 07:54:41',
                'resume' => 'Du 20 au 24 Juillet 2011 se déroulera le Festival International de danses et musiques traditionnelles de Wavre. Le samedi 23 Juillet, les Clap\'s Sabots sont invités à présenter une suite de danses wallonnes. Nous serons accompagné de l\'ensemble musical Arcamuse. Plus...',
                'content' => '<p>Du 20 au 24 Juillet 2011 se déroulera le <em><strong>Festival International de danses et musiques traditionnelles de Wavre</strong></em>.</p>
<p>Le samedi 23 Juillet, les <strong>Clap\'s Sabots</strong> sont invités à présenter une suite de danses wallonnes.</p>
<p>Nous serons accompagné de l\'ensemble musical <strong>Arcamuse</strong>.</p>
<p>Plus d\'informations sur le site : <a href="http://www.macadanse.be/">http://www.macadanse.be</a></p>',
                'image' => null,
            ],
            [
                'slug' => '36eme-feux-de-la-saint-jean-a-holleken',
                'tag' => 'Actualité',
                'title' => '36ème Feux de la Saint-Jean à Holleken',
                'date' => '2011-06-08 07:53:57',
                'resume' => 'A l\'occasion des 36ème feux de la Saint-Jean à Holleken, les Clap\'s Sabots présenteront 3 suites de danses. Deux suites de danses de Roumanie (Banat et Oltenie) et une suite de danses d\'Israël Passage prévu vers 18h Info Jabadao : 36èmes FEUX DE LA SAINT-JEAN le samedi 25 juin...',
                'content' => '<p>A l\'occasion des 36ème feux de la Saint-Jean à Holleken,</p>
<p>les Clap\'s Sabots présenteront 3 suites de danses.</p>
<p>Deux suites de danses de Roumanie (Banat et Oltenie) et une suite de danses d\'Israël</p>
<p>Passage prévu vers 18h</p>
<p><em>Info Jabadao :</em></p>
<p>36èmes FEUX DE LA SAINT-JEAN le samedi 25 juin 2011<br>
A la ferme ‘t Holleken, rue de Hollebeek, 212 à 1630 - Linkebeek<br>
De 15h à 1h du matin. ENTREE GRATUITE POUR TOUS<br>
Au programme : animations de danses folkloriques pour toutes les générations avec Brigitte Van Keer, René Vanderhasten et Jules Hauwaert.<br>
Contes avec Hélène Désirant et château gonflable dans la plaine.<br>
Spectacles des groupes : Les Cadets du Phénix, Art Folk, Les Clap’ Sabots et Jabadao. Snacks sur place.<br>
Cérémonie du feu à 20h30 par les Allumeurs sacrés et Stevarius. Avec la collaboration des musiciens du groupe Mosaïque.<br>
Renseignements : Nadine Van Der Steen – 0495/65.35.81<br>
Courrier électronique : jabadao@telenet.be</p>',
                'image' => null,
            ],
            [
                'slug' => '4eme-bal-de-danse-folklorique',
                'tag' => 'Actualité',
                'title' => '4ème Bal de Danse Folklorique',
                'date' => '2011-03-14 07:52:48',
                'resume' => 'Ce samedi 26 mars, l\'Ensemble Clap\'Sabots vous invite cordialement à son 4ème Bal de Danse Folklorique. L\'animation sera assurée par René Vanderhasten, qui vous attendra sur la piste de danse dès 20h. Lieu Salle St-Maarten Centrum Veldeke 1 1930 Zaventem PAF 7 euros à l\'entrée 6...',
                'content' => '<img src="http://www.quefaire.be/imgok/260179_3.jpeg" alt="" />Ce <strong>samedi 26 mars</strong>, l\'Ensemble Clap\'Sabots vous invite cordialement à son <strong>4ème Bal de Danse Folklorique</strong>.

L\'animation sera assurée par <strong>René Vanderhasten</strong>, qui vous attendra sur la piste de danse dès <strong>20h</strong>.

<strong>Lieu</strong>

Salle St-Maarten Centrum
Veldeke 1
1930 Zaventem

<strong>PAF</strong>
<ul>
 	<li>7 euros à l\'entrée</li>
 	<li>6 euros en prévente</li>
 	<li>gratuit jusqu\'à 12 ans.</li>
</ul>
<strong>Renseignements</strong>
<ul>
 	<li>contact@clapsabots.be</li>
 	<li>02 384 09 02 (journée)</li>
 	<li>02 384 02 34 (soir + répondeur)</li>
</ul>',
                'image' => 'http://www.quefaire.be/imgok/260179_3.jpeg',
            ],
            [
                'slug' => 'gala-informations-importantes-concernant-lacces-au-waux-hall',
                'tag' => 'Actualité',
                'title' => 'Gala: informations importantes concernant l\'accès au Waux-Hall',
                'date' => '2011-03-03 07:51:55',
                'resume' => 'Cette année, nous avons le plaisir de cotoyer, et la kermesse de Carnaval de la ville de Nivelles, et les travaux de rénovation de la grand place. Alors, pour vous éviter quelques désagréments de parking et de déplacement, nous vous conseillons de vous rendre directement aux...',
                'content' => '<p>Cette année, nous avons le plaisir de cotoyer, et la kermesse de Carnaval de la ville de Nivelles, et les travaux de rénovation de la grand place.</p>
<p>Alors, pour vous éviter quelques désagréments de parking et de déplacement, nous vous conseillons de vous rendre directement aux parkings publics indiqués sur les plans ci-dessous.<!--more--></p>
<p>Pour l\'accès en train depuis Bruxelles, nous vous conseillons de prendre le direct de 18h30 direction Charleroi. Pour le retour, le dernier train est à 23h56.</p>
<p>La gare de Nivelles se trouve à 20 minutes à pied du Waux-Hall.</p>
<p><a href="http://old.clapsabots.be/data/files/Images/grand%20place%20en%20travaux.png"><img title="accès ville" src="http://old.clapsabots.be/data/files/Images/grand%20place%20en%20travaux.png" alt="accès ville" width="500" height="281" /></a></p>
<p><a><img title="accès centre" src="http://old.clapsabots.be/data/files/Images/grand%20place%20en%20travaux%202.png" alt="accès centre" width="500" height="281" /></a></p>',
                'image' => 'http://old.clapsabots.be/data/files/Images/grand%20place%20en%20travaux.png',
            ],
            [
                'slug' => 'les-places-du-gala-2011-sont-des-a-present-en-vente',
                'tag' => 'Actualité',
                'title' => 'Les places du Gala 2011 sont dès à présent en vente',
                'date' => '2011-01-20 07:51:24',
                'resume' => 'Renseignements & réservations Par téléphone : 02/384.09.02 ou 0477/27.32.12 Par mail : contact@clapsabots.be',
                'content' => '<p><strong>Renseignements &amp; réservations</strong><br>
Par téléphone : 02/384.09.02 ou 0477/27.32.12<br>
Par mail : contact@clapsabots.be</p>',
                'image' => null,
            ],
            [
                'slug' => 'fetes-de-fin-dannee-2010',
                'tag' => 'Actualité',
                'title' => 'Fêtes de fin d\'année 2010',
                'date' => '2010-12-24 07:49:49',
                'resume' => 'L\'Ensemble Clap\'Sabots vous souhaite de Joyeuses Fêtes et beaucoup de bonne humeur !',
                'content' => '<p>L\'Ensemble Clap\'Sabots vous souhaite de Joyeuses Fêtes et beaucoup de bonne humeur !</p>',
                'image' => null,
            ],
            [
                'slug' => 'concert-de-noel-a-braine-lalleud',
                'tag' => 'Actualité',
                'title' => 'Concert de Noël à Braine-l\'Alleud',
                'date' => '2010-12-01 07:48:57',
                'resume' => 'Nouvelle édition du Concert de Noël, nouvelle formule aussi. Ce sera sous chapiteau, sur la Grand\'Place Baudouin Ier, au coeur même du marché de Noël, que les artistes enchanteront le public lors de deux représentations. Représentation le samedi 18 décembre à partir de 14h et le...',
                'content' => '<p><strong>Nouvelle édition du Concert de Noël, nouvelle formule aussi. Ce sera sous chapiteau, sur la Grand\'Place Baudouin Ier, au coeur même du marché de Noël, que les artistes enchanteront le public lors de deux représentations.</strong></p>
<p>Représentation le samedi 18 décembre à partir de 14h et le dimanche 19 décembre à partir de 12h30.</p>
<p>Organisé par le Centre culturel avec la participation des chorales « La Pastourelle », « La Rivelaine », Chœur « La Noucelles », l\'Harmonie Royale de Mont-Saint-Pont, la Société Royale d\'Harmonie, l\'Ensemble Clap’Sabots, l\'Académie de Musique et son école de danse, l\'Ecole des Arts, la Maison des jeunes « Le Prisme », O’Ben, Superfuel, avec le soutien de la Commune de Braine-l\'Alleud.</p>
<p>Gratuit !</p>
<p><strong>Infos </strong>: Centre culturel - 02 384 59 62.</p>',
                'image' => null,
            ],
            [
                'slug' => 'prestation-lors-du-bal-dautomne-du-botafogo-dance-club',
                'tag' => 'Actualité',
                'title' => 'Prestation lors du bal d\'Automne du Botafogo Dance Club',
                'date' => '2010-11-26 07:50:46',
                'resume' => 'C\'est à l\'occasion du Bal d\'Automne du Botafogo Dance Club (groupe de danses de salon) que nous monterons sur la piste ce 20 Novembre 2010. Le groupe y effectuera une suite de danses de Slovaquie et de Bulgarie. Lieu : Hall des sports de Ganshoren, 114 rue Vanderkinderen Affiche...',
                'content' => '<p>C\'est à l\'occasion du Bal d\'Automne du Botafogo Dance Club (groupe de danses de salon) que nous monterons sur la piste ce 20 Novembre 2010.</p>
<p>Le groupe y effectuera une suite de danses de Slovaquie et de Bulgarie.</p>
<p><strong>Lieu</strong> : Hall des sports de Ganshoren, 114 rue Vanderkinderen</p>
<p><strong>Affiche</strong> : <a href="http://www.jydanse.be/20nov%20Botafogo.pdf">Voir l\'affiche</a>5</p>',
                'image' => null,
            ],
            [
                'slug' => 'folk-2010',
                'tag' => 'Actualité',
                'title' => 'Folk 2010',
                'date' => '2010-11-12 07:50:17',
                'resume' => 'L\'Ensemble Clap\'Sabots ne sera malheureusement pas présent pour cette édition au Folk 2010. Trop peu de danseurs disponibles pour assurer un spectacle de qualité.',
                'content' => '<p>L\'Ensemble Clap\'Sabots ne sera malheureusement pas présent pour cette édition au Folk 2010.</p>
<p>Trop peu de danseurs disponibles pour assurer un spectacle de qualité.</p>',
                'image' => null,
            ],
            [
                'slug' => 'terroir-en-fete-a-wauthier-braine-le-dimanche-26-septembre',
                'tag' => 'Actualité',
                'title' => 'Terroir en fête à Wauthier-Braine le dimanche 26 Septembre',
                'date' => '2010-09-26 07:47:17',
                'resume' => 'Organisée conjointement à la kermesse de Wauthier-Braine, la Grand Place sera le théâtre de nombreuses animations telles que : marché artisanal et fermier, défilé d’anciens tracteurs, lancer de ballot, jeu de quille, jeux anciens, spectacle de danses folkloriques, et bien...',
                'content' => '<p>Organisée conjointement à la kermesse de Wauthier-Braine, la Grand Place sera le théâtre de nombreuses animations telles que : marché artisanal et fermier, défilé d’anciens tracteurs, lancer de ballot, jeu de quille, jeux anciens, spectacle de danses folkloriques, et bien d’autres encore …</p>
<p>Les Clap\'Sabots seront présent et proposeront plusieurs démonstrations</p>',
                'image' => null,
            ],
            [
                'slug' => 'reprise-des-cours',
                'tag' => 'Actualité',
                'title' => 'Reprise des cours',
                'date' => '2010-08-26 07:48:23',
                'resume' => 'La reprise des cours de danses "Enfants, Juniors, Jeunes, Vendredi" aura lieu ce vendredi 03 septembre aux heures habituelles Le cours du Lundi reprendra quant à lui le Lundi 06 septembre, aux heures habituelles. Nous vous y attendons tous en forme22',
                'content' => '<p>La reprise des cours de danses "Enfants, Juniors, Jeunes, Vendredi" aura lieu ce vendredi 03 septembre aux heures habituelles</p>
<p>Le cours du Lundi reprendra quant à lui le Lundi 06 septembre, aux heures habituelles.</p>
<p>Nous vous y attendons tous en forme22</p>',
                'image' => null,
            ],
            [
                'slug' => 'nouveaux-groupes-les-enfants-et-les-juniors',
                'tag' => 'Actualité',
                'title' => 'Nouveaux groupes: les enfants et les juniors.',
                'date' => '2009-02-04 07:46:05',
                'resume' => 'Après quelques années d\'absence, la section enfants des Clap\'s a été recrée en juin 2008. Ils sont déjà si nombreux, que nous avons dû ajouter une section supplémentaire. Les "Enfants", donc, de 5 à 7 ans sont accueillis tous les vendredis de 17h30 à 18h15 au Centre sportif...',
                'content' => '<p>Après quelques années d\'absence, la section <strong>enfants</strong> des Clap\'s a été recrée en juin 2008. Ils sont déjà si nombreux, que nous avons dû ajouter une section supplémentaire.</p>
<p>Les "<strong>Enfants</strong>", donc, de <strong>5 à 7 ans</strong> sont accueillis tous les <strong>vendredis de 17h30 à 18h15</strong> au Centre sportif Gaston Reiff de Braine-l\'Alleud.</p>
<p>Baptisée "<strong>J<em>uniors</em></strong>", la nouvelle section accueille les enfants entre <strong>7 et 12 ans</strong>, également<br>
au stade Gaston Reiff de Braine-l\'Alleud, tous les <strong>vendredis de 17h30 à 18h30</strong>.</p>
<p>N\'hésitez pas à venir essayer, premiers cours gratuits.</p>
<p>Pour de plus amples informations, vous pouvez visiter <a title="la page de la section" href="http://old.clapsabots.be/Groupe-junior.html">la </a>page de<a title="la page de la section" href="http://old.clapsabots.be/Groupe-junior.html"> la section</a> ou contacter les animatrices <strong>Nicole, Gaëlle</strong> et <strong>Evelyne</strong> via mail à l\'adresse suivante <strong>junior@clapsabots.be</strong></p>',
                'image' => null,
            ],
        ];
    }
}
