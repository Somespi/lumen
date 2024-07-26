<? 

enum AtomType {
    case Integer;
    case Float;
    case String;
    case Boolean;
    case Nil;
    case List; 
    case Function;
    case LiteralText;
}


class Atom {

    public $value;
    public $type;
    public $references = 0;

    public function __construct($value, AtomType $type) {
        $this->value = $value;
        $this->type = $type;
        $this->references += 1;
    }
}
