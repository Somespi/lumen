<?php 
enum Bytecode : int {
    case Push = 0;
    case Pop = 1;
    case Move = 2;
    case Dup = 3;
    case Add = 4;
    case Sub = 5;
    case Mult = 6;
    case Jump = 7;
    case JumpIf = 8;
    case JumpIfNot = 9;
    case NOP = 10;

    case EnterScope = 11;
    case ExitScope = 12;

}
