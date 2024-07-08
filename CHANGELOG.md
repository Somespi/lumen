**v0.1.2**:
- Added Modulo operator `%` for integer division
- Added `&&` and `||` operators
- Added Arrays using `array(1,2,3,...)` and slices with negative values using `array[start:stop:end]` *hence cannot use `array[:stop:end]`*

**v0.1.3**:
- Improved Echo's internal tokenization to be more efficient by using the same tokenization method as Lumen. Instead of relying on regex replacement, which is slower and harder to debug, we now use parsing. This change allows us to add an `internal_buffer`, enabling the program's output to be treated as a stack. As a result, we resolved the issue of Echo not working properly in loops and function calls.

**v0.1.31**:
- Added `+=`, `-=`, `*=`, `/=` and `%=` for arithmetic operators
- Added `|=` , `&=` and `^=` for bitwise operators
- Added `~` operator for bitwise not