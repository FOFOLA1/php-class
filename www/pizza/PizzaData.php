<?php


class PizzaComponent
{
    public function __construct(
        private string $name,
        private int $price
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): int
    {
        return $this->price;
    }
}

// Data configuration using objects
$sizes = [
    '30cm' => new PizzaComponent('30 cm', 120),
    '35cm' => new PizzaComponent('35 cm', 150),
    '40cm' => new PizzaComponent('40 cm', 180),
];

$bases = [
    'tomato' => new PizzaComponent('Tomatový základ', 0),
    'cream' => new PizzaComponent('Smetanový základ', 10),
];

$toppings = [
    'emmental' => new PizzaComponent('Ementál', 20),
    'eidam' => new PizzaComponent('Eidam', 20),
    'mozzarella' => new PizzaComponent('Mozzarella', 20),
    'niva' => new PizzaComponent('Niva', 20),
    'ham' => new PizzaComponent('Šunka', 25),
    'salami' => new PizzaComponent('Salám', 25),
    'bacon' => new PizzaComponent('Slanina', 25),
    'chicken' => new PizzaComponent('Kuřecí maso', 25),
    'mushrooms' => new PizzaComponent('Žampiony', 15),
    'onion' => new PizzaComponent('Cibule', 15),
    'peppers' => new PizzaComponent('Paprika', 15),
    'corn' => new PizzaComponent('Kukuřice', 15),
    'pineapple' => new PizzaComponent('Ananas', 15)
];

function getComponent(string $key, array $map): PizzaComponent
{
    return $map[$key] ?? new PizzaComponent($key, 0);
}
