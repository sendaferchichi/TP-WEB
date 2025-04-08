<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Combat Pokémon</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
class AttackPokemon {
    public $attackMinimal;
    public $attackMaximal;
    public $specialAttack;
    public $probabilitySpecialAttack;

    public function __construct($min, $max, $special, $proba) {
        $this->attackMinimal = $min;
        $this->attackMaximal = $max;
        $this->specialAttack = $special;
        $this->probabilitySpecialAttack = $proba;
    }
}

class Pokemon {
    protected $name;
    protected $url;
    protected $hp;
    protected $attackPokemon;

    public function __construct($name, $url, $hp, AttackPokemon $atk) {
        $this->name = $name;
        $this->url = $url;
        $this->hp = $hp;
        $this->attackPokemon = $atk;
    }

    public function isDead() {
        return $this->hp <= 0;
    }

    public function getHp() {
        return $this->hp;
    }

    public function getName() {
        return $this->name;
    }

    public function getUrl() {
        return $this->url;
    }

    public function whoAmI() {
        echo "<div class='pokemon'>";
        echo "<h3>{$this->name}</h3>";
        echo "<img src='{$this->url}' alt='{$this->name}' />";
        echo "<p>Points : {$this->hp}</p>";
        echo "<p>Min Attack Points : {$this->attackPokemon->attackMinimal}</p>";
        echo "<p>Max Attack Points : {$this->attackPokemon->attackMaximal}</p>";
        echo "<p>Special Attack : {$this->attackPokemon->specialAttack}</p>";
        echo "<p>Probability Special Attack : {$this->attackPokemon->probabilitySpecialAttack}</p>";
        echo "</div>";
    }

    public function attack(Pokemon $target) {
        $atk = rand($this->attackPokemon->attackMinimal, $this->attackPokemon->attackMaximal);
        $isSpecial = rand(1, 100) <= $this->attackPokemon->probabilitySpecialAttack;
        if ($isSpecial) $atk *= $this->attackPokemon->specialAttack;
        $target->takeDamage($atk);
        echo "<div class='round-log'>{$this->name} attaque {$target->name} pour $atk dégâts</div>";
    }

    public function takeDamage($damage) {
        $this->hp -= $damage;
    }
}

class PokemonFeu extends Pokemon {
    public function attack(Pokemon $target) {
        $multiplier = 1;
        if ($target instanceof PokemonPlante) $multiplier = 2;
        if ($target instanceof PokemonEau || $target instanceof PokemonFeu) $multiplier = 0.5;

        $atk = rand($this->attackPokemon->attackMinimal, $this->attackPokemon->attackMaximal);
        if (rand(1, 100) <= $this->attackPokemon->probabilitySpecialAttack) {
            $atk *= $this->attackPokemon->specialAttack;
        }
        $atk *= $multiplier;
        $target->takeDamage($atk);
        echo "<div class='round-log'>{$this->name} (Feu) attaque {$target->name} pour $atk dégâts</div>";
    }
}

class PokemonEau extends Pokemon {
    public function attack(Pokemon $target) {
        $multiplier = 1;
        if ($target instanceof PokemonFeu) $multiplier = 2;
        if ($target instanceof PokemonEau || $target instanceof PokemonPlante) $multiplier = 0.5;

        $atk = rand($this->attackPokemon->attackMinimal, $this->attackPokemon->attackMaximal);
        if (rand(1, 100) <= $this->attackPokemon->probabilitySpecialAttack) {
            $atk *= $this->attackPokemon->specialAttack;
        }
        $atk *= $multiplier;
        $target->takeDamage($atk);
        echo "<div class='round-log'>{$this->name} (Eau) attaque {$target->name} pour $atk dégâts</div>";
    }
}

class PokemonPlante extends Pokemon {
    public function attack(Pokemon $target) {
        $multiplier = 1;
        if ($target instanceof PokemonEau) $multiplier = 2;
        if ($target instanceof PokemonFeu || $target instanceof PokemonPlante) $multiplier = 0.5;

        $atk = rand($this->attackPokemon->attackMinimal, $this->attackPokemon->attackMaximal);
        if (rand(1, 100) <= $this->attackPokemon->probabilitySpecialAttack) {
            $atk *= $this->attackPokemon->specialAttack;
        }
        $atk *= $multiplier;
        $target->takeDamage($atk);
        echo "<div class='round-log'>{$this->name} (Plante) attaque {$target->name} pour $atk dégâts</div>";
    }
}

// Début du combat
$p1 = new PokemonFeu("Dracaufeu", "https://img.pokemondb.net/artwork/charizard.jpg", 200, new AttackPokemon(10, 100, 2, 20));
$p2 = new PokemonPlante("Mystherbe", "https://img.pokemondb.net/artwork/oddish.jpg", 200, new AttackPokemon(30, 80, 4, 20));

echo "<h1>Les combattants</h1><div class='container'>";
$p1->whoAmI();
$p2->whoAmI();
echo "</div>";

$round = 1;
while (!$p1->isDead() && !$p2->isDead()) {
    echo "<h2>Round $round</h2>";
    $p1->attack($p2);
    if (!$p2->isDead()) $p2->attack($p1);
    echo "<p>{$p1->getName()} HP: {$p1->getHp()} | {$p2->getName()} HP: {$p2->getHp()}</p>";
    $round++;
}

$winner = $p1->isDead() ? $p2 : $p1;
echo "<h2 class='winner'>Le vainqueur est : {$winner->getName()}</h2>";
?>

</body>
</html>
