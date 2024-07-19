<?php

class Program {
    public $body = [];
}

class EchoStatement {
    public $expression;
    public $pos;
    public $id;

    public function __construct($expression, $pos, $id) {
        $this->expression = $expression;
        $this->pos = $pos;
        $this->id = $id;
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

    public function __construct($name, $operator, $value) {
        $this->name = $name;
        $this->value = $value;
        $this->operator = $operator;
    }
}

class DeleteVariable {
    public $name;

    public function __construct($name) {
        $this->name = $name;
    }
}

class ObjectDeclare {
    public $name;
    public $body = [];
    public function __construct($name, $body) {
        $this->name = $name;
        $this->body = $body;
    }
}

class DeclareFunction {
    public $name;
    public $args;
    public $body;
    public $inclass;

    public function __construct($name, $args, $body, $inclass = FALSE) {
        $this->name = $name;
        $this->args = $args;
        $this->body = $body;
        $this->inclass = $inclass;
    }
}

class MemberAccess {
    public $object;
    public $property;
    
    public function __construct($object, $property) {
        $this->object = $object;
        $this->property = $property;
    }
}


class FunctionCall {
    public $name;
    public $args;

    public function __construct($name, $args) {
        $this->name = $name;
        $this->args = $args;
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

    public function __construct($value) {
        $this->value = $value;
    }
}

class StringLiteral {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class Identifier {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}

class IncludeStatement {
    public $filepath;

    public function __construct($filepath) {
        $this->filepath = $filepath;
    }
}

class ImportStatement {
    public $filepath;
    #public $alias;
    public function __construct($filepath) {
        $this->filepath = $filepath;
        #$this->alias = $alias;
    }
}

class ReturnStatement {
    public $value;

    public function __construct($value) {
        $this->value = $value;
    }
}
class Kill {}
class None {}
class BreakLoop {}
class ContinueLoop {}

class BinaryOperation {
    public $right;
    public $operator;
    public $left;

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}

class BoolOp {
    public $right;
    public $operator;
    public $left;

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}

class Subscript {
    public $identifier;
    public $slice;

    public function __construct($value, $slice) {
        $this->identifier = $value;
        $this->slice = $slice;
    }
}

class Slice {
    public $lower;
    public $upper;
    public $steps;

    public function __construct($lower=null, $upper=null, $steps=null) {
        $this->lower = $lower;
        $this->upper = $upper;
        $this->steps = $steps;
    }
}

class Index {
    public $value;

    public function __construct($value) {
        $this->value = $value;
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

    public function __construct($right, $operator, $left) {
        $this->right = $right;
        $this->operator = $operator;
        $this->left = $left;
    }
}

class UnaryOperation {
    public $operator;
    public $operand;

    public function __construct($operator, $operand) {
        $this->operator = $operator;
        $this->left = $operand;
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
    public function __construct($name, $value) {
        $this->left = $name;
        $this->value = $value;
    }
}
class LiteralText {
    public $value;
    public function __construct($value) {
        $this->value = $value;
    }
}


class FunctionObject {
    public $name;
    public $args;
    public $scope;
    public $body;

    public function __construct($name, $args, $body, $scope) {
        $this->name = $name;
        $this->args= $args;
        $this->body = $body;
        $this->scope = $scope;
    }
}

class ObjectType {
    public $name;
    public $properties;
    public $interpreter;

    public function __construct($name, $properties, $interpreter = null) {
        $this->name = $name;
        $this->properties = $properties;
        $this->interpreter = $interpreter;
    }
}

?>