![Logo](./resources/calhat.png)

**Calhat** is a lightweight and efficient language inspired by PHP and designed to streamline web development by offering a minimalistic syntax and powerful features. 


## Etymology
"Calhat" is inspired by the Omani historical city of Qalhat, as the city connected cultures in the past, Calhat aims to connect different programming ecosystems by exploring a cross-language approach between PHP, Node.js, and modern web frameworks.

## Features

- **Simplified Syntax:** Calhat offers a cleaner, more concise syntax compared to standard PHP, making code easier to write and maintain.

- **Modular Design:** Easy to extend with modules and plugins to suit various development needs.


## Example

```php
<?calhat

let rabbits = 80;
let foxes = 20;

def simulate_population() {
    let updated_rabbits = rabbits * 1.2;
    let updated_foxes = foxes * 0.8;

    foxes = updated_foxes;
    rabbits = updated_rabbits;


    echo "Yearly Population Update: \n";
    echo "Rabbits: " + rabbits + "\n";
    echo "Foxes:" + foxes + "\n"; 
}

let current_year = 1;
let years = 5;

loop current_year <= years {
    echo "\nYear " + current_year + "\n";
    simulate_population();
    current_year = current_year + 1;
}
?>

```

## Contributing

Contributions are welcome! Please fork the repository and submit a pull request with your improvements.

## License

Calhat is licensed under the MIT License. 
