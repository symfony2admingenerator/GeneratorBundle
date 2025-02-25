<?php

namespace Admingenerator\GeneratorBundle\Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\EmptyNode;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class ExtendsAdmingeneratedTokenParser extends AbstractTokenParser
{

    public function parse(Token $token): ?Node
    {
        $templateParts = explode(':', $this->parser->getCurrentToken()->getValue()); //AcmeBundle:namespace:template.html.twig
        if (count($templateParts) !== 3) {
          list($bundle, $folder, $file) = explode('/', $this->parser->getCurrentToken()->getValue()); //@Acme/namespace/template.html.twig
          $bundle = sprintf('%sBundle', substr($bundle, 1));
        } else {
          list($bundle, $folder, $file) = $templateParts;
        }

        $path = "Admingenerated/$bundle/Resources/views/$folder/$file";

        $this->parser->getExpressionParser()->parseExpression();

        $this->parser->setParent(new ConstantExpression($path,$token->getLine()));
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return new EmptyNode($token->getLine());
    }

    public function getTag(): string
    {
        return 'extends_admingenerated';
    }
}
