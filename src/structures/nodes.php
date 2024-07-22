<?php

class Program {
    public $body = [];
}

class EchoStatement {
    public $expression;
    public $pos;

    public function __construct($expression, $pos) {
        $this->expression = $expression;
        $this->pos = $pos;
    }
}

class DeclareVariable {
    public $name;
    public $value;
    public $pos; 

    public function __construct($name, $value, $pos) {
        $this->name = $name;
        $this->value = $value;
        $this->pos = $pos;
    }
}

class AugAssign {
    public $name;
    public $operator; 
    public $value;
    public $pos;

    public function __construct($name, $operator, $value, $pos) {
        $this->name = $name;
        $this->value = $value;
        $this->operator = $operator;
        $this->pos = $pos;
    }
}

class DeleteVariable {
    public $name;
    public $pos;

    public function __construct($name, $pos) {
        $this->name = $name;
        $this->pos = $pos;
    }
}

class ObjectDeclare {
    public $name;
    public $body = [];
    public $pos;

    public function __construct($name, $body, $pos) {
        $this->name = $name;
        $this->body = $body;
        $this->pos = $pos;
    }
}

class DeclareFunction {
    public $name;
    public $args;
    public $body;
    public $inclass;
    public $pos;

    public function __construct($name, $args, $body, $pos, $inclass = FALSE) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
        $this->inclass = $inclass;
        $this->pos = $pos;
    }
}

class MemberAccess {
    public $object;
    public $property;
    public $pos;

    public function __construct($object, $property, $pos) {
        $this->object = $object;
        $this->property = $property;
        $this->pos = $pos;
    }
}

class FunctionCall {
    public $name;
    public $args;
    public $pos;

    public function __construct($name, $args, $pos) {
        $this->name = $name;
        $this->args = $args;
        $this->pos = $pos;
    }
}

class IfStatement {
    public $condition;
    public $body = [];
    public $tryother = [];
    public $else;
    public $pos; 

    public function __construct($condition, $body, $tryother, $else, $pos) {
        $this->condition = $condition;
        $this->body = $body;
        $this->pos = $pos;
        $this->tryother = $tryother;
        $this->else = $else;
    }
}

class NumberLiteral {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class StringLiteral {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class Identifier {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class IncludeStatement {
    public $filepath;
    public $pos;

    public function __construct($filepath, $pos) {
        $this->filepath = $filepath;
        $this->pos = $pos;
    }
}

class ImportStatement {
    public $filepath;
    public $pos;

    public function __construct($filepath, $pos) {
        $this->filepath = $filepath;
        $this->pos = $pos;
    }
}

class ReturnStatement {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class Kill {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class None {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class BreakLoop {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class ContinueLoop {
    public $pos;

    public function __construct($pos) {
        $this->pos = $pos;
    }
}

class BinaryOperation {
    public $right;
    public $operator;
    public $left;
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class BoolOp {
    public $right;
    public $operator;
    public $left;
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class Subscript {
    public $identifier;
    public $slice;
    public $pos;

    public function __construct($value, $slice, $pos) {
        $this->identifier = $value;
        $this->slice = $slice;
        $this->pos = $pos;
    }
}

class Slice {
    public $lower;
    public $upper;
    public $steps;
    public $pos;

    public function __construct($lower=null, $upper=null, $steps=null, $pos) {
        $this->lower = $lower;
        $this->upper = $upper;
        $this->steps = $steps;
        $this->pos = $pos;
    }
}

class Index {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

enum Comparison
{
    case Gt;
    case Lt;
    case LtE;
    case GtE;
    case Is;
    case IsNot;
}

enum Unary
{
    case Not;
    case Minus;
    case Negate;
}

enum Operation
{
    case Add;
    case Sub;
    case Div;
    case Mult;
    case Modulo;
    case BitAnd;
    case BitOr;
}

enum BoolOperand
{
    case And;
    case Or;
}

class Compare {
    public $right;
    public $operator;
    public $left;
    public $pos;

    public function __construct($right, $operator, $left, $pos) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
        $this->pos = $pos;
    }
}

class UnaryOperation {
    public $operator;
    public $operand;
    public $pos;

    public function __construct($operator, $operand, $pos) {
        $this->operator = $operator;
        $this->operand = $operand;
        $this->pos = $pos;
    }
}

class LoopStatement {
    public $condition;
    public $body;
    public $pos;

    public function __construct($condition, $body, $pos) {
        $this->condition = $condition;
        $this->body = $body;
        $this->pos = $pos;
    }
}

class AssignVariable {
    public $left;
    public $value;
    public $pos;

    public function __construct($name, $value, $pos) {
        $this->left = $name;
        $this->value = $value;
        $this->pos = $pos;
    }
}

class LiteralText {
    public $value;
    public $pos;

    public function __construct($value, $pos) {
        $this->value = $value;
        $this->pos = $pos;
    }
}

class FunctionObject {
    public $name;
    public $args;
    public $scope;
    public $body;
    public $pos;

    public function __construct($name, $args, $body, $scope, $pos) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
        $this->scope = $scope;
        $this->pos = $pos;
    }
}

class ObjectType {
    public $name;
    public $properties;
    public $interpreter;
    public $pos;

    public function __construct($name, $properties, $pos, $interpreter = null) {
        $this->name = $name;
        $this->properties = $properties;
        $this->interpreter = $interpreter;
        $this->pos = $pos;
    }
}

