<?php

namespace Admingenerator\GeneratorBundle\Twig\TokenParser;

use Twig\Error\SyntaxError;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

class ExtendsAdmingeneratedTokenParser extends AbstractTokenParser
{

    /**
     * Parses a token and returns a node.
     *
     * @param Token $token A Twig Token instance
     *
     * @return Node A Twig Node instance
     */
    public function parse(Token $token)
    {
        if (null !== $this->parser->getParent()) {
            throw new SyntaxError('Multiple extends tags are forbidden', $token->getLine());
        }

        list($bundle, $folder, $file) = explode(':', $this->parser->getCurrentToken()->getValue());

        $path = "Admingenerated/$bundle/Resources/views/$folder/$file";

        $this->parser->getExpressionParser()->parseExpression();

        $this->parser->setParent(new ConstantExpression($path,$token->getLine()));
        $this->parser->getStream()->expect(Token::BLOCK_END_TYPE);

        return null;
    }

    /**
     * Gets the tag name associated with this token parser.
     *
     * @return string The tag name
     */
    public function getTag()
    {
        return 'extends_admingenerated';
    }
}
