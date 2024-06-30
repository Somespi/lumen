# Lumen
Lumen is a custom programming language designed to blend seamlessly with HTML. It features simple syntax for variable declarations, function definitions, control flow, and more.
**MOCKUP PHP BASICILLY**


## Lumen Language Reference

#### Table of Contents

1. [Lexical Structure](#lexical-structure)
   - [Keywords](#keywords)
   - [Identifiers](#identifiers)
   - [Literals](#literals)
   - [Operators](#operators)
2. [Syntax](#syntax)
   - [Program Structure](#program-structure)
   - [Statements](#statements)
     - [Variable Declaration](#variable-declaration)
     - [Assignment](#assignment)
     - [Function Declaration](#function-declaration)
     - [Function Call](#function-call)
     - [Conditional Statements](#conditional-statements)
     - [Loops](#loops)
     - [Echo](#echo)
     - [Die](#die)
3. [Expressions](#expressions)
   - [Arithmetic Expressions](#arithmetic-expressions)
   - [Comparison Expressions](#comparison-expressions)
5. [Functions](#functions)
6. [Scope and Lifetime](#scope-and-lifetime)
7. [Error Handling](#error-handling)
8. [Examples](#examples)


#### Lexical Structure

###### Keywords

Lumen reserves the following keywords:

- `let`
- `def`
- `echo`
- `die`
- `del`
- `if`
- `elif`
- `else`
- `loop`
- `break`
- `none`
- `continue`
- `set`
- `return`

###### Identifiers

Identifiers in Lumen are names given to variables and functions. They must letters followed digits or letters.

###### Literals

Lumen supports the following literals:

- **Numeric Literals:** e.g., `123`, `45.67`
- **String Literals:** e.g., `"hello"`, `'world'`

###### Operators

Lumen supports the following operators:

- **Arithmetic Operators:** `+`, `-`, `*`, `/`
- **Comparison Operators:** `>`, `<`, `>=`, `<=`, `==`, `!=`
- **Logical Operators:** `&&`, `||`, `!`
- **Unary Operators:** `-`, `!`


#### Syntax

###### Program Structure

A Lumen program consists of a series of statements and expressions.

##### Statements

###### Variable Declaration

```js
let variableName = expression;
```

###### Assignment

```kotlin
set variableName = expression;
```

###### Function Declaration

```py
def functionName(arg1, arg2, ...) {
    ...
}
```

###### Function Call

```php
functionName(arg1, arg2, ...);
```

###### Conditional Statements

```py
if condition {
    ...
} elif condition {
    ...
} else {
    ...
}
```

###### Loops

```rs
loop condition {
    ...
}
```

###### Echo

```php
echo expression;
```

###### Die

```php
die;
```

#### Expressions

###### Arithmetic Expressions

Arithmetic expressions use the arithmetic operators:

```ts
let result = 1 + 2 * 3 - 4 / 5;
```

###### Comparison Expressions

Comparison expressions use the comparison operators:

```ts
let aIsGreater = a > b;
```


#### Functions

Functions in Lumen are declared using the `def` keyword and can be called with arguments.

```php
def myFunction(arg1, arg2) {
    ...
}
```

#### Scope and Lifetime

Variables declared with `let` inside a function are local to that function. Global variables are accessible throughout the program.

you can de-declare any variable with `del` keyword, which is being called for every local-defined variable in the scope before exiting.

#### Error Handling

Lumen provides basic error handling with the `die` statement, which terminates the program execution.

```php
die;
```

#### Examples

###### Hello World

```php
<?lumen
echo "Hello, World!";
?>
```

###### Variable Declaration and Assignment

```php
<?lumen
let a = 10;
a = 20;
?>
```

###### Function Declaration and Call

```php
<?lumen
def add(a, b) {
    return a + b;
}

echo add(5, 3);
?>
```

###### Conditional Statements

```php
<?lumen
let a = 5;
if (a > 3) {
    echo "a is greater than 3";
} else {
    echo "a is not greater than 3";
}
?>
```

###### Loop

```php
<?lumen
let i = 0;
loop (i < 5) {
    echo i;
    set i = i + 1;
}
?>
```
