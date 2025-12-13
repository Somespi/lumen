# Lumen 

Lumen is a lightweight programming language designed with a custom parser, interpreter, and virtual machine (still in work). It features dynamic typing, object-oriented structures, functions, control flow, and a stack-based execution model.

# Features

**AST-based Interpreter** – Walks the syntax tree for execution.

**Bytecode Virtual Machine** – Translates high-level code into bytecode for efficient execution.

**Dynamic Variables & Objects** – Supports functions, objects, and member access.

**Control Flow** – if, elif, else, loop, return.

**Error Diagnostics** – Lexical, syntax, runtime, and import errors with source snippets.

**Import System** – Modular code execution and object encapsulation.


## Architecture Overview

1. **Lexer** – Converts source code into tokens.


2. **Parser** – Generates an abstract syntax tree (AST) from tokens.


3. **Interpreter / VM** – Executes the AST or compiled bytecode.


4. **Symbol Table & Scope Handling** – Tracks variables, functions, and object properties.


5. **Error Handling** – Provides clear feedback with file, line, and code snippets.



Example
```
let x = 10;
let y = 20;
echo x + y;   # outputs 30

def add(a, b) {
    return a + b;
}

echo add(x, y); # outputs 30
```
