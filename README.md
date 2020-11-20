# SushiBar

Externe Quellen:
* https://getbootstrap.com BootstrapCSS
* https://unsplash.com/photos/wmPDe9OnXT4 Sushi Bild von Luigi Pozzoli

Klassen:
* Sushibar.class.php
* Seat.class.php
* Group.class.php

## Sushibar.class.php

Diese Klasse beinhaltet die Sushibar und speichert Objekte der Seat- und Group-Klasse.
```php
private $seat_amount;
private $seats;
private $name;
private $guests = [];
        
/**
    * __construct
    * Konstruktor erstellt die Sushibar
    *
    * @param  string $name
    * @param  int $seats
    * @return void
    */
function __construct($name, $seats) {

    $seats = intval($seats);

    if ( $name === "" ) {
        $name = "F&P Sushibar";
    }
    
    if ( $seats == null || $seats === 0 ) {
        $seats = 10;
    }

    $this->name = $name;
    $this->createSeats($seats);
    $this->seat_amount = $seats;

}
```

Im Konstruktor wird die Sushibar erstellt und in $seats werden Objekte der Klasse Seat als Array gespeichert.
