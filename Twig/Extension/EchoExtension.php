<?php

namespace Admingenerator\GeneratorBundle\Twig\Extension;

/**
 * @author Cedric LOMBARDOT
 * @author Piotr Gołębiewski <loostro@gmail.com>
 * @author Stéphane Escandell
 */
class EchoExtension extends \Twig_Extension
{
    /**
     * @var bool
     */
    private $useExpression;

    public function __construct($useExpression = false)
    {
        $this->useExpression = $useExpression;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            'echo_if_granted'     => new \Twig_SimpleFunction('echo_if_granted', array($this, 'getEchoIfGranted')),
            'echo_path'           => new \Twig_SimpleFunction('echo_path', array($this, 'getEchoPath')),
            'echo_trans'          => new \Twig_SimpleFunction('echo_trans', array($this, 'getEchoTrans')),
            'echo_render'         => new \Twig_SimpleFunction('echo_render', array($this, 'getEchoRender'))
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'convert_as_form' => new \Twig_SimpleFilter('convert_as_form', array($this, 'convertAsForm')),
        );
    }

    /**
     * Print "trans" tag for string $str with parameters $parameters
     * for catalog $catalog.
     *
     * @param $str
     * @param  array  $parameters
     * @param  string $catalog
     * @return string
     */
    public function getEchoTrans($str, array $parameters = array(), $catalog = 'Admingenerator', $escape = null)
    {
        $transParameters='{}';
        $bag_parameters=array();

        if ($parameterBag = $this->getParameterBag($str)) {
            $str = $parameterBag['string'];
            $bag_parameters = $parameterBag['params'];
        }

        if (!empty($parameters) || !empty($bag_parameters)) {
            $transParameters="{";

            foreach ($parameters as $key => $value) {
                $transParameters.= "'%".$key."%': '".str_replace("'", "\'", $value)."',";
            }
            foreach ($bag_parameters as $key => $value) {
                $transParameters.= "'%".$key."%': ".str_replace("'", "\'", $value).",";
            }

            $transParameters.="}";
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
     *
     * @param $path
     * @param  array        $params
     * @param  array|string $filters
     * @return string
     */
    public function getEchoPath($path, $params = null, $filters = null)
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
     *
     * @param $credentials
     * @param  null   $modelName
     * @return string
     */
    public function getEchoIfGranted($credentials, $modelName = null)
    {
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
     *
     * @param $controller
     * @param  array  $params
     * @return string
     */
    public function getEchoRender($controller, array $params = array())
    {
        $params = $this->getTwigAssociativeArray($params);

        return '{{ render(controller("'.$controller.'", '.$params.')) }}';
    }

    /**
     * Try to convert options of form given as string from yaml to a good object
     *
     * eg type option for collection type
     *
     * @param string $options  the string as php
     * @param string $formType the form type
     *
     * @return string the new options
     */
    public function convertAsForm($options, $formType)
    {
        // Transforms PHP call into PHP (simple copy/paste)
        $options = preg_replace("/'__php\((.+?)\)'/i", '$1', $options, -1, $count);

        // Converts 'type' => '\My\Fully\QualifiedType' into 'type' => new \My\Fully\QualifiedType()
        // (mainly used in collections
        preg_match("/'type' => '(.+?)'/i", $options, $matches);
        if (count($matches) > 0) {
            $pattern_formtype = '/^\\\\+(([a-zA-Z_]\w*\\\\+)*)([a-zA-Z_]\w*Type)$/';
            // Sanity check: prepend with "new" and append with "()"
            // only if type option is a Fully qualified name
            if (preg_match($pattern_formtype, $matches[1])) {
                $options = str_replace("'type' => '".$matches[1]."'", '\'type\' => new '.stripslashes($matches[1]).'()', $options);
            }
        }

        // Query builder
        if (preg_match("#^entity#i", $formType) || preg_match("#entity$#i", $formType) ||
            preg_match("#^document#i", $formType) || preg_match("#document$#i", $formType)) {
            preg_match("/'query_builder' => '(.+?)',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'query_builder' => '".$matches[1]."'", '\'query_builder\' => '.stripslashes($matches[1]), $options);
            }
        }

        if (preg_match("#^model#i", $formType) || preg_match("#model$#i", $formType)) {
            preg_match("/'query' => '(.+?)',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'query' => '".$matches[1]."'", '\'query\' => '.stripslashes($matches[1]), $options);
            }
        }

        // Choices
        if ('choice' == $formType || 'double_list' == $formType) {
            preg_match("/'choices' => '(.+?)',/i", $options, $matches);

            if (count($matches) > 0) {
                $options = str_replace("'choices' => '".$matches[1]."'", '\'choices\' => '.stripslashes($matches[1]), $options);
            }
        }

        if (preg_match('#^(.+)Type$#i', $formType) || 'form_widget'== $formType) { // For type wich are not strings
            preg_match("/\'(.*)Type\'/", $options, $matches);

            if (count($matches) > 0) {
                return 'new '.stripslashes($matches[1]).'Type()';
            }
        }

        return $options;
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
    private function getParameterBag($subject)
    {
        // Backwards compability - replace twig tags with parameters
        $pattern_bc = '/\{\{\s(?<param>[a-zA-Z0-9.]+)\s\}\}+/';

        if (preg_match_all($pattern_bc, $subject, $match_params)) {
            $string = preg_filter($pattern_bc, '%\1%', $subject);

            $param = array();
            foreach ($match_params['param'] as $value) {
                $param[$value] = $value;
            }

            return array(
                'string' => $string,
                'params' => $param
            );
        }

        # Feature - read key/value syntax parameters
        $pattern_string = '/^(?<string>[^|]+)(?<parameter_bag>\|\{(\s?%[a-zA-Z0-9.]+%:\s[a-zA-Z0-9.]+,?\s?)+\s?\}\|)\s*$/';
        $pattern_params = '/(?>(?<=(\|\{\s|.,\s))%(?<key>[a-zA-Z0-9.]+)%:\s(?<value>[a-zA-Z0-9.]+)(?=(,\s.|\s\}\|)))+/';

        if (preg_match($pattern_string, $subject, $match_string)) {
            $string = $match_string['string'];
            $parameter_bag = $match_string['parameter_bag'];

            $param = array();
            preg_match_all($pattern_params, $parameter_bag, $match_params, PREG_SET_ORDER);

            foreach ($match_params as $match) {
                $param[$match['key']] = $match['value'];
            }

            return array(
                'string' => $string,
                'params' => $param
            );
        }

        # Feature - read abbreviated syntax parameters
        $abbreviated_pattern_string = '/^(?<string>[^|]+)(?<parameter_bag>\|\{(\s?[a-zA-Z0-9.]+,?\s?)+\s?\}\|)\s*$/';
        $abbreviated_pattern_params = '/(?>(?<=(\|\{\s|.,\s))(?<param>[a-zA-Z0-9.]+)(?=(,\s.|\s\}\|)))+?/';

        if (preg_match($abbreviated_pattern_string, $subject, $match_string)) {
            $string = $match_string['string'];
            $parameter_bag = $match_string['parameter_bag'];

            $param = array();
            preg_match_all($abbreviated_pattern_params, $parameter_bag, $match_params);

            foreach ($match_params['param'] as $value) {
                $param[$value] = $value;
            }

            return array(
                'string' => $string,
                'params' => $param
            );
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
    private function getTwigAssociativeArray(array $hashmap)
    {
        $contents = array();
        foreach ($hashmap as $key => $value) {
            if (!strstr($value, '{{') || !strstr($value, '}}')) {
                $value = "'$value'";
            } else {
                $value = trim(str_replace(array('{{', '}}'), '', $value));
            }

            $contents[] = "$key: $value";
        }

        return '{ ' . implode(', ', $contents) . ' }';
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'admingenerator_echo';
    }
}
