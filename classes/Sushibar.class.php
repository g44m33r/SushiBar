<?php

    include "Seat.class.php";
    include "Group.class.php";

    class Sushibar {

        private $seat_amount;
        private $seats;
        private $name;
        private $groups = [];
                
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
            
            if ( $seats <= 0 ) {
                $seats = 10;
            }

            $this->name = $name;
            $this->createSeats($seats);
            $this->seat_amount = $seats;

        }

        /**
         * createSeats
         * Füllt das private Array $this->seats mit Seat Objekten
         *
         * @param  int $amount Anzahl an Sitzplätzen
         * @return void
         */
        function createSeats($amount) {

            $tmp = [];
            for( $i = 0; $i < $amount; $i++) {
                array_push($tmp, new Seat($i));
            }
            $this->seats = $tmp;
            
        }
        
        /**
         * clearSeats
         * Gibt alle Sitzplätze wieder frei
         *
         * @return void
         */
        function clearSeats() {

            foreach ($this->seats as $seat) {
                $seat->setFree();
            }

            $this->groups = [];
        }
      
        /**
         * getName
         * Gibt den Namen der Sushibar zurück
         * @return string $this->name
         */
        function getName() {
            return $this->name;
        }
        
        /**
         * getGroups
         * Gibt ein Array an Guest Objekten zurück
         * @return Guest[]
         */
        function getGroups() {
            return $this->groups;
        }
        
        /**
         * groupLeave
         * Entfernt eine Gruppe und gibt die Sitzplätze wieder frei
         * @param  int $id Gruppenid
         * @return boolean
         */
        function groupLeave($id) {

            $id = intval($id);

            if ( array_key_exists($id, $this->groups) ) {
                foreach ($this->groups[$id]->getSeats() as $seat) {
                    // echo $seat->getStatus();
                    $seat->setFree();
                    unset($this->groups[$id]);
                }
    
                # Array index neu erstellen
                $this->groups = array_values($this->groups);

                return true;

            } else {
                return false;
            }

        }
        
        /**
         * getAvailalbeSeats
         * Gibt die Anzahl an Sitzplätzen die insgesamt zur Verfügung stehen zurück
         * @return int
         */
        function getAvailalbeSeats() {
            return $this->seat_amount;
        }
                
        /**
         * getSeats
         * Gibt ein Array an Seat Elementen zurück die insgesamt zur Verfügung stehen
         * @return Seat[] 
         */
        function getSeats() {
            $tmpSeats = [];

            foreach ($this->seats as $seat) {
                array_push($tmpSeats, $seat);
            }

            return $tmpSeats;
        }
        
        /**
         * incomingGuests
         * Diese Funktion wird aufgerufen wenn neue Gäste ins Lokal kommen und der Sushimeister prüft ob noch Plätze vorhanden sind
         * Falls ja dann werden die Plätze belgt und die Gäste als Gruppe eingetragen
         * Gibt false oder ein Array an Seat Elementen zurück die durch die neuen Gäste belegt wurden
         * @param  int $amount
         * @return boolean
         * @return Seat[]
         */
        function incomingGuests($amount) {
            
            // Zu int convertieren da über $POST per String
            // Ansonsten funktioniert === nicht mehr da keine automatische konvertierung
            $amount = intval($amount);

            # Prüfen ob schon Sitzplätze im Lokal belegt sind, falls nein dann bei Sitzplatz 0 beginnen
            if( $this->freeSeats() === $this->seat_amount ) {
                # Seat them at Seat 0 since everything is free
                return $this->seatGuests($amount, array("seatID" => 0, "direction" => "right"));

            } else {
                # Finden des bestmöglichen Platzes anhand des Score
                $check = $this->findFreeSeats($amount);
                
                if ( $check ) {
                    # Sitzplätze gefunden -> Gäste können sich setzen
                    return $this->seatGuests($amount, $check);             
                } else {
                    return false;
                }
            }
            


        }
        
        /**
         * freeSeats
         * Gibt die Anzahl an freien Sitzplätzen zurück
         * @return int
         */
        function freeSeats() {
            $free = 0;

            foreach ($this->seats as $seat) {
                if ( $seat->isFreeSeat() ) {
                    $free++;
                }
            }

            return $free;
        }
        
        /**
         * Rekursive Funktion um die Sitzplätze zu durchlaufen
         * $currentSeat gibt hierbei den aktuellen Sitzplatz an
         *
         * @param  int $currentSeat - Sitzplatz an dem gestartet wird
         * @param  int $remainingSeats - Anzahl an übrigen Sitzplätzen die zu Prüfen sind
         * @param  string $direction - Richtung in die geprüft wird ( right / left)
         * @return int
         */
        function rec_findFreeSeat($currentSeat, $remainingSeats, $direction) {

            # Prüfen ob wir über das Array hinausgehen in Richtung -
            if ( $direction == "left" && $currentSeat < 0 ) {
                $currentSeat = count($this->seats)-1;
            }

            # Prüfen ob wir über das Array hinausgehen in Richtung +
            if ( $direction == "right" && $currentSeat > count($this->seats)-1 ) {
                $currentSeat = 0;
            }
        
            # Prüfen ob noch Sitzplätze zu prüfen sind um rekursion zu unterbrechen
            if ( $remainingSeats > 0 ) {

                if ( $direction == "left" ) {

                    if ( $this->seats[$currentSeat]->isFreeSeat() ) {
                        return 1 + $this->rec_findFreeSeat($currentSeat-1, $remainingSeats-1, $direction);
                    } else {
                        return 0 + $this->rec_findFreeSeat($currentSeat-1, $remainingSeats-1, $direction);
                    }

                } else {

                    if ( $this->seats[$currentSeat]->isFreeSeat() ) {
                        return 1 + $this->rec_findFreeSeat($currentSeat+1, $remainingSeats-1, $direction);
                    } else {
                        return 0 + $this->rec_findFreeSeat($currentSeat+1, $remainingSeats-1, $direction); 
                    }

                }

            }

        }
        
        /**
         * seatGuests
         * Diese Funktion belegt die Sitzplätze $seats für eine Gruppe der Größe $groupSize
         * @param  int $groupSize
         * @param  Seat[] $seats
         * @return boolean/Seat[] False oder Seat Array
         */
        function seatGuests($groupSize, $seats) {

            $startingSeat = $seats["seatID"];
            $nowOccupiedSeats = [];

            for( $i = 0; $i < $groupSize; $i++ ) {

                if( $seats["direction"] === "left" ) {
                    if ( $startingSeat < 0 ) {
                        $startingSeat = count($this->seats)-1;
                    }
                    # Nochmal prüfen ob Sitzplatz nicht doch belegt ist
                    if ( $this->seats[$startingSeat]->isFreeSeat() ) {
                        $this->seats[$startingSeat]->setOccupied();
                        array_push($nowOccupiedSeats, $this->seats[$startingSeat]);
                        $startingSeat--;
                    } else {
                        return false;
                    }
                } else {
                    if ( $startingSeat > count($this->seats)-1 ) {
                        $startingSeat = 0;
                    }
                    # Nochmal prüfen ob Sitzplatz nicht doch belegt ist
                    if ( $this->seats[$startingSeat]->isFreeSeat() ) {
                        $this->seats[$startingSeat]->setOccupied();
                        array_push($nowOccupiedSeats, $this->seats[$startingSeat]);
                        $startingSeat++;
                    } else {
                        return false;
                    }
                }

            }

            # Gruppe erstellen, damit beim verlassen die Sitzplätze freigegebene werden können
            array_push($this->groups, new Group($groupSize, $nowOccupiedSeats));
            return $nowOccupiedSeats;
        }
        
        
        /**
         * findFreeSeats
         * Funktion um freie Sitzplätze für die Gruppengröße $groupAmount zu finden
         * Gibt ein Array an möglichen Sitzplätzen zurück
         * @param  int $groupAmount
         * @return array inkl. Seat Score
         */
        function findFreeSeats($groupAmount) {
            $possibleStartingPoints = [];

            # Durchlaufen der Sitzplätze

            foreach ($this->seats as $id => $seat) {

                if ( $seat->isFreeSeat() ) {
                    # Sitz ist frei

                    # Prüfen ob rec_findFreeSeat() die Zahl an $groupAmount in die jeweilige Richtung zurückgibt
                    # Falls ja dann sind die nötigen Plätze in dieser Richtung frei
                    # Somit wird der aktuelle Sitz als Startpunkt + Richtung dem Array hinzugefügt
                    if ( $this->rec_findFreeSeat($id, $groupAmount, "left") === $groupAmount ) {
                        array_push($possibleStartingPoints, array("seatID" => $id, "direction" => "left"));
                    }
                    if ( $this->rec_findFreeSeat($id, $groupAmount, "right") === $groupAmount ) {
                        array_push($possibleStartingPoints, array("seatID" => $id, "direction" => "right"));
                    } 
                }


            }

            # Wenn das Array $possibleStartingPoints leer ist gibt es keine Sitzplätze für die Anzahl an Personen
            # die verschiedenen Sitzplätze werden jetzt bewertet um so wenig Platz wie möglich zu verschwenden
            return $this->evaluateStartingPoints($possibleStartingPoints,$groupAmount);

        }
        
        /**
         * checkSeatsUntilReachingOccupiedSeat
         * Diese Funktion gibt die Anzahl an freien Sitzplätzen in die jeweilige Richtung zurück
         * Dies wird zur Bewertung der Sitzplätze benötigt
         * @param  int $startingPoint
         * @param  string $direction
         * @return int
         */
        function checkSeatsUntilReachingOccupiedSeat($startingPoint, $direction) {

            # Prüfen ob wir über das Array hinausgehen in Richtung -
            if ( $direction == "left" && $startingPoint < 0 ) {
                $startingPoint = count($this->seats)-1;
            }

            # Prüfen ob wir über das Array hinausgehen in Richtung +
            if ( $direction == "right" && $startingPoint > count($this->seats)-1 ) {
                $startingPoint = 0;
            }

            if ( $direction === "left" ) {
                if ( $this->seats[$startingPoint]->isFreeSeat() ) {
                    return 1 + $this->checkSeatsUntilReachingOccupiedSeat($startingPoint-1, $direction);
                } else {
                    return 0;
                }
            } else {
                if ( $this->seats[$startingPoint]->isFreeSeat() ) {
                    return 1 + $this->checkSeatsUntilReachingOccupiedSeat($startingPoint+1, $direction);
                } else {
                    return 0;
                }
            }

        }
        
        /**
         * checkSingleSeat
         * Diese Funktion gibt 1 bzw 0 zurück je nachdem ob der Nachbarsitz in Richtung $direction frei ist oder nicht
         * @param  int $seat
         * @param  string $direction
         * @return int
         */
        function checkSingleSeat($seat, $direction) {
            # Prüfen ob wir über das Array hinausgehen in Richtung -
            if ( $direction == "left" && $seat < 0 ) {
                $seat = count($this->seats) - 1;
            }

            # Prüfen ob wir über das Array hinausgehen in Richtung +
            if ( $direction == "right" && $seat > count($this->seats)-1 ) {
                $seat =  0;
            }

            if ( $this->seats[$seat]->isFreeSeat() ) {
                return 1;
            } else {
                return 0;
            }
        }
        
        /**
         * evaluateStartingPoints
         * $amount gibt hier die Größe der Gruppe an die Sitzen möchte
         * $possibleStartingPoints ist das Array an Sitzplätzen mit der jeweiligen Richtung die für die Gruppe in Frage kommen
         * @param  Seat[] $possibleStartingPoints
         * @param  int $amount
         * @return array Bester Platz wird zurückgegeben
         */
        function evaluateStartingPoints($possibleStartingPoints,$amount) {

            # je niedriger der Score desto besser
            # Bester Sitzplatz ist wenn der Score = $amount
            # Falls so ein Sitzplatz nicht exisitert, wird der mit den meisten freien Sitzen in eine Richtung und einem belegten Sitz in die andere Richtung genommen

            foreach ($possibleStartingPoints as $id => $point) {

                # Wenn nach links gegangen wird, dann nur in die andere Richtung abprüfen
                if( $point["direction"] === "left" ) {
                    $possibleStartingPoints[$id]["score"] = $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "left");
                    # Prüfen ob Sitzplatz in anderer Richtung belget ist, falls ja dann nichts dazuaddieren, ansonsten rekursiv
                    if ( $this->checkSingleSeat($point["seatID"]+1, "right") ) {
                        $possibleStartingPoints[$id]["score"] = $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "left") + $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "right"); 
                    } else {
                        $possibleStartingPoints[$id]["score"] = $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "left");
                    }
                } else {
                    if ( $this->checkSingleSeat($point["seatID"]-1, "left") ) {
                        $possibleStartingPoints[$id]["score"] = $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "right") + $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "left");
                    } else {
                        $possibleStartingPoints[$id]["score"] = $this->checkSeatsUntilReachingOccupiedSeat($point["seatID"], "right");  
                    }  
                                      
                }
            }

            # Array nach Score sortieren (aufsteigen --> bester Wert auf Key 0)
            $scores = array_column($possibleStartingPoints, 'score');
            $starting_scores = array_multisort($scores, SORT_ASC, $possibleStartingPoints);

            return $possibleStartingPoints[0]; // Bester Seat mit bestem Score da vorher sortiert

        }
        
    }

?>