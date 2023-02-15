<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Symfony\Component\Form\Extension\Core\DataTransformer\MoneyToLocalizedStringTransformer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Piotr Gołębiewski <loostro@gmail.com>
 */
class LocalizedMoneyExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            'localized_money' => new TwigFunction('localized_money', $this->getLocalizedMoney(...)),
            'currency_sign'   => new TwigFunction('currency_sign', $this->getCurrencySign(...)),
        ];
    }

    /**
     * @param float $value Money amount.
     *
     * @param string|false $currency This can be any 3 letter ISO 4217 code. You
     * can also set this to false to hide the currency symbol.
     *
     * @param integer $precision For some reason, if you need some precision
     * other than 2 decimal places, you can modify this value. You probably
     * won't need to do this unless, for example, you want to round to the
     * nearest dollar (set the precision to 0).
     *
     * @param string|bool $grouping This value is used internally as the
     * NumberFormatter::GROUPING_USED value when using PHP's NumberFormatter
     * class. Its documentation is non-existent, but it appears that if you set
     * this to true, numbers will be grouped with a comma or period (depending
     * on your locale): 12345.123 would display as 12,345.123.
     *
     * @param integer $divisor If, for some reason, you need to divide your
     * starting value by a number before rendering it to the user, you can use
     * the divisor option.
     *
     * @return string Localized money
     */
    public function getLocalizedMoney(float $value, string|false $currency = 'EUR', int $precision = 2, string|bool $grouping = true, int $divisor = 1): string
    {
        $locale = \Locale::getDefault();

        $format = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $pattern = $format->formatCurrency('123', $currency);

        $dt = new MoneyToLocalizedStringTransformer($precision, $grouping, null, $divisor);
        $transformed_value = $dt->transform($value);

        preg_match('/^([^\s\xc2\xa0]*)[\s\xc2\xa0]*123(?:[,.]0+)?[\s\xc2\xa0]*([^\s\xc2\xa0]*)$/u', $pattern, $matches);

        if (!empty($matches[1])) {
            $localized_money = $matches[1].' '.$transformed_value;
        } elseif (!empty($matches[2])) {
            $localized_money = $transformed_value.' '.$matches[2];
        } else {
            $localized_money = $transformed_value;
        }

        return $localized_money;
    }

    /**
     * @param string|false $currency This can be any 3 letter ISO 4217 code. You
     * can also set this to false to return the general currency symbol.
     *
     * @return string Currency sign
     */
    public function getCurrencySign(string|false $currency = false): string
    {
        $locale = \Locale::getDefault();

        $format = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        $pattern = $format->formatCurrency('123', $currency);

        preg_match('/^([^\s\xc2\xa0]*)[\s\xc2\xa0]*123(?:[,.]0+)?[\s\xc2\xa0]*([^\s\xc2\xa0]*)$/u', $pattern, $matches);

        if (!empty($matches[1])) {
            $currency_sign = $matches[1];
        } elseif (!empty($matches[2])) {
            $currency_sign = $matches[2];
        } else {
            $currency_sign = '¤';
        }

        return $currency_sign;
    }

    public function getName(): string
    {
        return 'localized_money';
    }
}
