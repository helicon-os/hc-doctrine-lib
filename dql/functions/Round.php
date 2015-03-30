<?php

/*
 * Copyright (c) 2015, Andreas Prucha, Helicon Software Development
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace helicon\doctrine\lib\dql\functions;

/**
 * Doctrine/DQL ROUND function
 * 
 * ROUND (number, decimals)
 * 
 * <b>number</b>: * Numeric expression
 * <b>decimals</b>: Scale (Number of decimals). Default = 0
 * 
 * <b>Returns:</b> Rounded number
 * 
 * This function can be used for all database systems supporting the SQL ROUND function like MySql, MSSql, Sqlite, etc
 *
 * @author Andreas Prucha, Helicon Software Development
 */
class Round extends FunctionNode
{

    /**
     * Number parameter
     *
     * @var mixed
     */
    public $numberParam;

    /**
     * Scale parameter
     *
     * @var mixed
     */
    public $scaleParam = 0;

    /**
     * getSql
     *
     * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'ROUND(' .
                $sqlWalker->walkSimpleArithmeticExpression($this->numberParam) . ',' .
                $sqlWalker->walkStringPrimary($this->scaleParam) .
                ')';
    }

    /**
     * @param \Doctrine\ORM\Query\Parser $parser
     * @access public
     * @return void
     */
    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->numberParam = $parser->SimpleArithmeticExpression();
        if ($parser->getLexer()->lookahead['type'] == Lexer::T_COMMA)
        {
            $parser->match(Lexer::T_COMMA);
            $this->scaleParam = $parser->ArithmeticExpression();
        };
        if (empty($this->scaleParam))
            $this->scaleParam = 0;
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

}
