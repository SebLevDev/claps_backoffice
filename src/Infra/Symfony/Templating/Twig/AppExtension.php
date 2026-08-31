<?php

namespace Infra\Symfony\Templating\Twig;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getName(): string
    {
        return 'twigext';
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('phoneNumber', [$this, 'phoneNumber']),
            new TwigFilter('ceil', [$this, 'ceil']),
            new TwigFilter('mailTo', [$this, 'mailTo'], ['is_safe' => ['html']]),
            new TwigFilter('phoneTo', [$this, 'phoneTo'], ['is_safe' => ['html']]),
            new TwigFilter('repeat', [$this, 'repeat']),
            new TwigFilter('hrFilesize', [$this, 'hrFilesize']),
            new TwigFilter('getClassName', [$this, 'getClassName']),
            new TwigFilter('localizedDate', [$this, 'localizedDate'], ['needs_environment' => true]),
            new TwigFilter('country', [$this, 'country']),
            new TwigFilter('countryFlag', [$this, 'countryFlag']),
            new TwigFilter('languageName', [$this, 'languageName']),
        ];
    }


    public function phoneNumber(string $string): string
    {
        $string = trim($string);
        $newString = '';
        if ((strlen($string) == 12) && (substr($string,0,3) == '+32')) {
            // Numéros belges format international
            $newString = substr($string,0,3).' '.substr($string,3,3).' '.substr($string,6,2).' '.substr($string,8,2).' '.substr($string,10,2);

        } elseif ((strlen($string) == 10) && (substr($string,0,2) == '04')) {
            // N° de GSM format local
            $newString = substr($string,0,4).' '.substr($string,4,2).' '.substr($string,6,2).' '.substr($string,8,2);

        } elseif ((strlen($string) == 9) && (substr($string,0,2) == '02')) {
            // Préfix Bruxelles
            $newString = substr($string,0,2).' '.substr($string,2,3).' '.substr($string,5,2).' '.substr($string,7,2);
        }

        // Ultime vérif
        if (str_replace(' ','',$newString) == $string) {
            return $newString;
        }

        return $string;
    }

    public function ceil($string)
    {
        $string = trim($string);

        return ceil($string);
    }

    public function mailTo($string, $show_icon = false): string
    {
        $string = trim($string);
        $out = '<a href="mailto:'.$string.'">';
        if ($show_icon) {
            $out .= '<i class="fa fa-envelope-o"></i>&nbsp;';
        }
        $out .= $string.'</a>';

        return $out;
    }

    public function phoneTo(string $string): ?string
    {
        $string = trim($string);

        return '<a href="tel:' . $string . '">' . $string . '</a>';
    }

    public function repeat($input, $multiplier): string
    {
        return str_repeat($input, $multiplier);
    }

    public function hrFilesize($size): string
    {
        $filesizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");

        return $size ? round($size/1024 ** ($i = floor(log($size, 1024))), 2) . $filesizename[$i] : '0 Bytes';
    }

    public function getClassName($object): ?string
    {
        if (is_object($object)) {
            return $object::class;
        }

        return null;
    }

    /**
     * Display a localized DateTime based on a pattern
     * @param \Twig_Environment $env (http://framework.zend.com/manual/1.12/en/zend.date.constants.html#zend.date.constants.selfdefinedformats)
     * @param $pattern
     * @return string
     */
    public function localizedDate(\Twig_Environment $env, \DateTime $datetime, $pattern)
    {
        $date = twig_date_converter($env, $datetime, $env->getExtension('Twig_Extension_Core')->getTimezone());

        $formatValues = [
            'none'   => \IntlDateFormatter::NONE,
            'short'  => \IntlDateFormatter::SHORT,
            'medium' => \IntlDateFormatter::MEDIUM,
            'long'   => \IntlDateFormatter::LONG,
            'full'   => \IntlDateFormatter::FULL,
        ];

        $formatter = \IntlDateFormatter::create(
            null,
            $formatValues['medium'],
            $formatValues['medium'],
            $datetime->getTimezone()->getName(),
            \IntlDateFormatter::GREGORIAN,
            $pattern
        );

        return $formatter->format($date->getTimestamp());

    }

    public function country(?string $country_code): string
    {
        if (!$country_code) {
            return '';
        }

        return Countries::getName($country_code);
    }

    /**
     * Convertit un code pays ISO 3166-1 alpha-2 (ex: "BE", "FR") en émoji drapeau (🇧🇪, 🇫🇷),
     * en combinant les deux "regional indicator symbols" Unicode correspondant aux lettres du code.
     * N'a besoin d'aucune image/police additionnelle, s'affiche nativement partout.
     */
    public function countryFlag(?string $country_code): string
    {
        $country_code = strtoupper(trim((string) $country_code));

        if (!preg_match('/^[A-Z]{2}$/', $country_code)) {
            return '';
        }

        $flag = '';
        foreach (str_split($country_code) as $letter) {
            $flag .= mb_chr(0x1F1E6 + (\ord($letter) - \ord('A')), 'UTF-8');
        }

        return $flag;
    }

    public function languageName(string $language_code, string $output_language = 'en'): string
    {
        return Languages::getName($language_code, $output_language);
    }

}
