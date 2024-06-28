<?php

namespace Core\Database;

use InvalidArgumentException;

class Operators
{
    const EQUALS = '=';
    const NOT_EQUALS = '!=';
    const LESS_THAN = '<';
    const GREATER_THAN = '>';
    const LESS_THAN_EQUAL_TO = '<=';
    const GREATER_THAN_EQUAL_TO = '>=';
    const LIKE = 'LIKE';
    const NOT_LIKE = 'NOT LIKE';
    const BETWEEN = 'BETWEEN';
    const NOT_BETWEEN = 'NOT BETWEEN';
    const IN = 'IN';
    const NOT_IN = 'NOT IN';
    const IS_NULL = 'IS NULL';
    const IS_NOT_NULL = 'IS NOT NULL';
    const REGEXP = 'REGEXP';
    const AND = 'AND';
    const OR = 'OR';
    const NOT = 'NOT';
    const VALID_WHERE_OPERATORS = ['=', '<', '>', '<=', '>=', '!=', 'LIKE', 'NOT LIKE', 'BETWEEN', 'NOT BETWEEN', 'IN', 'NOT IN', 'IS NULL', 'IS NOT NULL', 'REGEXP'];

    const ORDER_DIRECTION_ASC = 'ASC';
    const ORDER_DIRECTION_DESC = 'DESC';


    public static function isValidWhereOperator(string $operator): bool
    {
        return in_array(trim($operator), self::VALID_WHERE_OPERATORS);
    }


    public static function getWherePlaceholder(string $operator, $value = null): string
    {
        $placholder = '';
        switch ($operator) {
            case self::IS_NULL:
            case self::IS_NOT_NULL: {
                // do nothing
                break;
            }

            case self::BETWEEN:
            case self::NOT_BETWEEN: {
                if (is_null($value) || !is_array($value) || (count($value) != 2))
                    throw new InvalidArgumentException("$operator Operator requires array of count 2.");
                $placholder = ' ? AND ? ';
                break;
            }

            case self::IN:
            case self::NOT_IN: {
                if (is_null($value) || !is_array($value) or empty($value))
                    throw new InvalidArgumentException("$operator Operator requires a non-empty array.");
                $placholder = ' (' . trim(str_repeat(' ? , ', count($value)), '\,\ ') . ') ';
                break;
            }

            default: {
                if (is_null($value) || is_array($value))
                    throw new InvalidArgumentException("$operator Operator requires int,float or string value.");
                $placholder = ' ? ';
                break;
            }
        }


        return $placholder;
    }


}