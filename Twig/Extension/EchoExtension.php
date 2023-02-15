<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @author Cedric LOMBARDOT
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell
 */
class EchoExtension extends AbstractExtension
{

    public function __construct(private readonly bool $useExpression = false)
    {
    }

    public function getFunctions(): array
    {
        $options = ['is_safe' => ['html']];
        return [
            'echo_if_granted' => new TwigFunction('echo_if_granted', $this->getEchoIfGranted(...), $options),
            'echo_path'       => new TwigFunction('echo_path', $this->getEchoPath(...), $options),
            'echo_trans'      => new TwigFunction('echo_trans', $this->getEchoTrans(...), $options),
            'echo_render'     => new TwigFunction('echo_render', $this->getEchoRender(...), $options)
        ];
    }

    public function getFilters(): array
    {
        $options = ['is_safe' => ['html']];
        return [
            'convert_as_form' => new TwigFilter('convert_as_form', $this->convertAsForm(...), $options),
        ];
    }

    /**
     * Try to convert options of form given as string from yaml to a good object
     *    > Transforms PHP call into PHP :
     *      addFormOptions:
     *          myOption: __php(MyStaticClass::myCustomFunction())
     *
     *    > Tranforms [query_builder|query] into valid Closure:
     *      addFormOptions:
     *          query_builder: function($er) { return $er->createMyCustomQueryBuilder(); }
     */
    public function convertAsForm(string $options, string $formType): string
    {
        // Transforms PHP call into PHP (simple copy/paste)
        preg_match("/'__php\((.+?)\)'/i", stripslashes($options), $matches);
        if (count($matches)) {
            $options = preg_replace("/'__php\((.+?)\)'/i", $matches[1], $options);
        }

        // Query builder: remove quotes around closure
        // Should we really check formType or can we just
        // look for query_builder option?
        if (preg_match("/EntityType$/i", $formType)) {
            preg_match("/'query_builder' => '(.+?)}',/i", $options, $matches);

            if (count($matches) > 0) {
              $options = str_replace("'query_builder' => '$matches[1]}'", "'query_builder' => ".stripslashes($matches[1]).'}', $options);
            }
            preg_match("/'query_builder' => '(.+?)',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'query_builder' => '$matches[1]'", "'query_builder' => ".stripslashes($matches[1]), $options);
            }
        }

        // Same question here
        if (preg_match("/ModelType$/i", $formType)) {
            preg_match("/'query' => '(.+?)}',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'query' => '$matches[1]}'", "'query' => ".stripslashes($matches[1]).'}', $options);
            }
            preg_match("/'query' => '(.+?)',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'query' => '$matches[1]'", "'query' => ".stripslashes($matches[1]), $options);
            }
        }

        return $options;
    }

    /**
     * Print "trans" tag for string $str with parameters $parameters
     * for catalog $catalog.
     */
    public function getEchoTrans(string $str, array $parameters = [], string $catalog = 'Admingenerator', bool $escape = null): string
    {
        $transParameters = '{}';
        $bag_parameters = [];

        if ($parameterBag = $this->getParameterBag($str)) {
            $str = $parameterBag['string'];
            $bag_parameters = $parameterBag['params'];
        }

        if (!empty($parameters) || !empty($bag_parameters)) {
            $transParameters = "{";

            foreach ($parameters as $key => $value) {
                $transParameters .= "'%".$key."%': '".str_replace("'", "\'", $value)."',";
            }
            foreach ($bag_parameters as $key => $value) {
                $transParameters .= "'%".$key."%': ".str_replace("'", "\'", $value).",";
            }

            $transParameters .= "}";
        }

        return sprintf(
            '{{ "%s"|trans(%s, "%s")%s }}',
            str_replace('"', '\"', $str),
            $transParameters,
            $catalog,
            $escape ? sprintf('|escape("%s")', $escape) : '|raw'
        );
    }

    /**
     * Print "echo tag with path call" to the path $path with params $params.
     */
    public function getEchoPath(string $path, array $params = null, array|string $filters = null): string
    {
        if (null === $params) {
            return (null === $filters)
                ? strtr('{{ path("%%path%%") }}', array('%%path%%' => $path))
                : strtr(
                    '{{ path("%%path%%")|%%filters%% }}',
                    array(
                        '%%path%%' => $path,
                        '%%filters%%' => (is_array($filters) ? implode('|', $filters) : $filters)
                    )
                );
        }

        $params = preg_replace('/\{\{\s+?([\w\.]+)\s+?\}\}/i', '$1', $params);

        return (null === $filters)
            ? strtr('{{ path("%%path%%", %%params%%) }}', array('%%path%%' => $path, '%%params%%' => $params))
            : strtr(
                '{{ path("%%path%%", %%params%%)|%%filters%% }}',
                array(
                    '%%path%%' => $path,
                    '%%params%%' => $params,
                    '%%filters%%' => (is_array($filters) ? implode('|', $filters) : $filters)
                )
            );
    }

    /**
     * Print "if" tag with condition to is_expr_granted('$credentials')
     * If $modelName is not null, append the $modelName to the function call.
     */
    public function getEchoIfGranted(string $credentials, string $modelName = null): string
    {
        if ('AdmingenAllowed' == $credentials) {
            return "{% if (true) %}";
        }

        return sprintf(
            "{%% if %s('%s'%s) %%}",
            $this->useExpression ? 'is_expr_granted' : 'is_granted',
            $credentials,
            $modelName ? ', '.$modelName.' is defined ? '.$modelName.' : null' : ''
        );
    }

    /**
     * Print "echo tag with render call" to the controller $controller
     * with $params parameters.
     */
    public function getEchoRender(string $controller, array $params = []): string
    {
        $params = $this->getTwigAssociativeArray($params);

        return '{{ render(controller("'.$controller.'", '.$params.')) }}';
    }

    /**
     * Reads parameters from subject and removes parameter bag from string.
     *
     * @return array
     *               [string] -> string for echo trans
     *               [params] -> parameters for echo trans
     *
     * @return false if subject did not match any of following patterns
     *
     * ##############################
     * Backwards compability pattern:
     *
     * replaces twig tags {{ parameter_name }} with parameters.
     *
     * example: You're editing {{ Book.title }} written by {{ Book.author.name }}!
     *
     * results in:
     *   string -> You're editing %Book.title% written by %Book.author.name%!
     *   params ->
     *     [Book.title] -> Book.title
     *     [Book.author.name] -> Book.author.name
     *
     * ###################################
     * Feature - key-value syntax pattern:
     * |{ %param_key%: param_value, %param_key2%: param_value2, %param_key3%: param_value3 }|
     *
     * where param_key and param_value consist of any number a-z, A-Z, 0-9 or . (dot) characters
     *
     * example: You're editing %book% written by %author%!|{ %book%: Book.title, %author%: Book.author.name }|
     * results in:
     *   string -> You're editing %book% written by %author%!
     *   params ->
     *     [book] -> Book.title
     *     [author] -> Book.author.name
     *
     * example: book.edit.title|{ %book%: Book.title, %author%: Book.author.name }|     *
     * results in:
     *   string -> book.edit.title
     *   params ->
     *     [book] -> Book.title
     *     [author] -> Book.author.name
     *
     * ###################################
     * Feature - abbreviated syntax pattern:
     * |{ param_value, param_value2, param_value3 }|
     *
     * where param_value consists of any number a-z, A-Z, 0-9 or . (dot) characters
     *
     * example: You're editing %Book.title% written by %Book.author.name%!|{ Book.title, Book.author.name }|
     * results in:
     *   string -> You're editing %Book.title% written by %Book.author.name%!
     *   params ->
     *     [Book.title] -> Book.title
     *     [Book.author.name] -> Book.author.name
     *
     * example: book.edit.title|{ Book.title, Book.author.name }|
     * results in:
     *   string -> book.edit.title
     *   params ->
     *     [Book.title] -> Book.title
     *     [Book.author.name] -> Book.author.name
     */
    private function getParameterBag(array $subject): array|false
    {
        // Backwards compability - replace twig tags with parameters
        $pattern_bc = '/\{\{\s(?<param>[a-zA-Z0-9.]+)\s\}\}+/';

        if (preg_match_all($pattern_bc, $subject, $match_params)) {
            $string = preg_filter($pattern_bc, '%\1%', $subject);

            $param = [];
            foreach ($match_params['param'] as $value) {
                $param[$value] = $value;
            }

            return [
                'string' => $string,
                'params' => $param
            ];
        }

        # Feature - read key/value syntax parameters
        $pattern_string = '/^(?<string>[^|]+)(?<parameter_bag>\|\{(\s?%[a-zA-Z0-9.]+%:\s[a-zA-Z0-9.]+,?\s?)+\s?\}\|)\s*$/';
        $pattern_params = '/(?>(?<=(\|\{\s|.,\s))%(?<key>[a-zA-Z0-9.]+)%:\s(?<value>[a-zA-Z0-9.]+)(?=(,\s.|\s\}\|)))+/';

        if (preg_match($pattern_string, $subject, $match_string)) {
            $string = $match_string['string'];
            $parameter_bag = $match_string['parameter_bag'];

            $param = [];
            preg_match_all($pattern_params, $parameter_bag, $match_params, PREG_SET_ORDER);

            foreach ($match_params as $match) {
                $param[$match['key']] = $match['value'];
            }

            return [
                'string' => $string,
                'params' => $param
            ];
        }

        # Feature - read abbreviated syntax parameters
        $abbreviated_pattern_string = '/^(?<string>[^|]+)(?<parameter_bag>\|\{(\s?[a-zA-Z0-9.]+,?\s?)+\s?\}\|)\s*$/';
        $abbreviated_pattern_params = '/(?>(?<=(\|\{\s|.,\s))(?<param>[a-zA-Z0-9.]+)(?=(,\s.|\s\}\|)))+?/';

        if (preg_match($abbreviated_pattern_string, $subject, $match_string)) {
            $string = $match_string['string'];
            $parameter_bag = $match_string['parameter_bag'];

            $param = [];
            preg_match_all($abbreviated_pattern_params, $parameter_bag, $match_params);

            foreach ($match_params['param'] as $value) {
                $param[$value] = $value;
            }

            return [
                'string' => $string,
                'params' => $param
            ];
        }

        // If subject does not match any pattern, return false
        return false;
    }

    /**
     * Converts an assoc array to a twig array expression (string) .
     * Only in case a value contains '{{' and '}}' the value won't be
     * wrapped in quotes.
     *
     * An array like:
     * <code>
     * $array = array('a' => 'b', 'c' => 'd', 'e' => '{{f}}');
     * </code>
     *
     * Will be converted to:
     * <code>
     * "{ a: 'b', c: 'd', e: f }"
     * </code>
     *
     * @return string
     */
    private function getTwigAssociativeArray(array $hashmap): string
    {
        $contents = array();
        foreach ($hashmap as $key => $value) {
            if (!str_contains($value, '{{') || !str_contains($value, '}}')) {
                $value = "'$value'";
            } else {
                $value = trim(str_replace(array('{{', '}}'), '', $value));
            }

            $contents[] = "$key: $value";
        }

        return '{ ' . implode(', ', $contents) . ' }';
    }

    public function getName(): string
    {
        return 'admingenerator_echo';
    }
}
