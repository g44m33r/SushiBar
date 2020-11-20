<?php
  /*
    F&B Sushibar by Christoph Rebhan
  */

  include "classes/Sushibar.class.php";
  include "config.inc.php";

  session_start();

  # Falls noch keine Sushibar angelegt ist --> neue Sushibar anlegen und in Session speichern
  if ( $_SESSION['Sushibar'] == "" ) {
    $_SESSION['Sushibar'] = new Sushibar($config["name"], $config["sitzplaetze"]);
  }

  # Gruppe geht
  if ( isset($_POST["groupLeaving"]) ) {

    $grp = intval($_POST["groupLeaving"]);

    if ( $_SESSION['Sushibar']->groupLeave($grp) ) {
      $result = "?action=groupLeaving&result=true";
      header( "Location:". $_SERVER['REQUEST_URI'].$result, true, 303 );
      exit();
    } else {
      $result = "?action=groupLeaving&result=false";
      header( "Location:". $_SERVER['REQUEST_URI'].$result, true, 303 );
      exit();
    }

  }

  # Neue Gäste kommen an
  if ( isset($_POST["addGuests"]) ) {
    $seats = $_SESSION['Sushibar']->incomingGuests($_POST["amount"]);
   if ( $seats ) {
      # Freie Plätze gefunden
      echo '<p class="text-success">Freie Plätze gefunden! <p/>';
      echo '<p>Bitte setzt euch auf die folgenden Plätze: <p/>';
      
      echo '<div class="d-flex">';
      foreach ($seats as $seat) {
        $seatstring .= $seat->getSeatID().",";
        echo '<div class="seat mb-2 mr-2">'. $seat->getSeatID().'</div>';  
      }
      $seatstring = substr($seatstring, 0 , -1);
      echo '</div>';

      $result = "?action=addGuests&result=true&seats=".$seatstring;
      header( "Location:". $_SERVER['REQUEST_URI'].$result, true, 303 );
      exit();

   } else {

     # leider keine freien Plätze mehr
     $result = "?action=addGuests&result=false";
     header( "Location:". $_SERVER['REQUEST_URI'].$result, true, 303 );
     exit();

   }
}

  # Sitze leeren
  if ( isset($_POST["clearSeats"]) ) {
    $_SESSION['Sushibar']->clearSeats();
    header( "Location:". $_SERVER['REQUEST_URI'], true, 303 );
    exit();
  }

  # Session löschen
  if ( isset($_POST["clearSession"]) ) {
    session_destroy();
    header( "Location:". $_SERVER['REQUEST_URI'], true, 303 );
    exit();
  }


?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>F&P Sushibar - by Christoph Rebhan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <link href="assets/bootstrap.min.css" rel="stylesheet">
    <link href="assets/own.css" rel="stylesheet">
</head>
</body>

<div class="container mt-4">
  <!--
    Oberste CARD mit Name, Belegung und neue Gäste Funktion
  -->
  <div class="card mb-4 shadow-sm sushi">
    <div class="d-flex flex-column justify-content-between card-body text-light">
      <!-- 
        Heading der Hauptkarte mit Name und Belegungszahl 
      -->
      <div class="d-flex text-white justify-content-between align-items-center">
          <p>
            <?php 
              /**
               * Namen der Sushibar ausgeben
               */
              echo $_SESSION['Sushibar']->getName();
            ?>
          </p>
          <p>Freie Sitzplätze: <span class="badge badge-primary">
            <?php
              /**
               * Anzahl Sitzplätze + verfügbaren Plätze ausgeben
               * 
               */
                echo $_SESSION['Sushibar']->freeSeats() . " / ". $_SESSION['Sushibar']->getAvailalbeSeats();
            ?> 
          </span></p>         
      </div>

      <!-- 
        Form zum eingeben neuer Gäste 
      -->
      <div class="text-center">
      <form method="POST" action="index.php" class="form-inline">
        <div class="form-group">
          <select class="form-control mb-2 mr-sm-2" name="amount">
            <?php

              /**
               * <option> Möglichkeiten anhand maximaler Plätze erstellen
               */

              for ( $i = 1; $i <= $_SESSION['Sushibar']->getAvailalbeSeats(); $i++ ) {
                echo "<option> $i </option>";
              }

            ?>
          </select>
          <button type="submit" name="addGuests" class="btn btn-primary mb-2 mr-sm-2">Gäste Platzieren</button>
        </div>
      </form>
      </div>

    </div>
    <!-- 
      Ausgabe des Ergebnisses vom einfügen neuer Gäste
      Card-Footer wird nur erzeugt wenn Ausgabe vorhanden ist
    -->
    <?php
      # Addguest result
      if ( (isset($_GET["action"] )) && ( $_GET["action"] == "addGuests" ) ) {

        echo '<div class="card-footer bg-light">';

        if ( $_GET["result"] == "true") {
          echo '<p class="text-dark">Freie Plätze gefunden! Bitte setzt euch auf die folgenden Plätze: <p/>';
          
          echo '<div class="d-flex">';

          $seats = explode(",",$_GET['seats']);
          foreach ($seats as $seat) {
            echo '<div class="seat mb-2 mr-2">'. $seat.'</div>';  
          }
          $seatstring = substr($seatstring, 0 , -1);
          echo '</div>';
        } else {
          echo '<div class="alert alert-danger">Leider keine Plätze mehr frei.</div>';
        }

        echo '</div>';

      }
    ?>
  </div>
  
  <!--
    Sitzplatzübersicht mit Legende
  -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header">
      Sitzplatzübersicht
    </div>
    <div class="card-body">

      <div class="d-flex justify-content-between align-content-stretch align-items-center flex-wrap">
        <?php
            foreach ($_SESSION['Sushibar']->getSeats() as $seat) {
              if ( $seat->isFreeSeat() ) {
                echo '<div class="seat free mb-2">'. $seat->getSeatID().'</div>';
              } else {
                echo '<div class="seat occupied mb-2">'. $seat->getSeatID().'</div>';
              }
            }

            /**
             * Platz Nummer 0 nochmals anzeigen, um ringförmiges anordnen zu verdeutlichen
             */
            if ( $_SESSION['Sushibar']->getSeats()[0]->isFreeSeat() ) {
              echo '<div class="seat free mb-2">'. $_SESSION['Sushibar']->getSeats()[0]->getSeatID().'</div>';
            } else {
              echo '<div class="seat occupied mb-2">'. $_SESSION['Sushibar']->getSeats()[0]->getSeatID().'</div>';
            }
        ?>
      </div>
      <?php

          error_reporting(-1);
          error_reporting(E_ALL);

          // $_SESSION['Sushibar']->getSeats();
          // $fpSushibar->getSeats();
          // $fpSushibar->incomingGuests(3);

      ?>
    </div>
    <!-- Legende -->
    <div class="card-footer d-flex justify-content-center">
      <div class="d-flex">
        <div class="dot occupied mr-2"></div>
        <span class="text-occupied">belegt</span>
      </div>
      <div class="d-flex ml-4">
        <div class="dot free mr-2"></div>
        <span class="text-free">frei</span>
      </div>
    </div>
  </div>

  <!--
    Gästeliste
  -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header">
      <div class="d-flex justify-content-between align-items-center">
        Gästeliste
      </div>
    </div>
    <div class="card-body">
    <?php
      # GET Result vom Gehen der Gruppe
      if ( (isset($_GET["action"])) && ( $_GET["action"] == "groupLeaving" ) ) {
        if ( $_GET["result"] == "true") {
          echo '<div class="alert alert-success">Gruppe ist gegangen. Sitzplätze sind wieder frei!</div>';
        } else {
          echo '<div class="alert alert-danger">Fehler! Die Gruppe existiert nicht.</div>';
        }
      }
    ?>

    <?php

      // Prüfen ob Gruppen vorhanden sind bzw. Gäste da sind.
      if ( $_SESSION['Sushibar']->freeSeats() === $_SESSION['Sushibar']->getAvailalbeSeats() ) {
        echo '<div class="text-center">Noch keine Gäste anwesend!</div>';
      } else {

    ?>
    <table class="table table-bordered table-striped">
      <thead class="thead-dark">
        <tr>
          <th scope="col" class="text-center">#ID</th>
          <th scope="col" class="text-center">Anzahl Pers.</th>
          <th scope="col" class="text-center">Sitzplätze</th>
          <th scope="col" class="text-center">Aktion</th>
        </tr>
      </thead>
      <tbody>

        <?php
            # Gästegruppen ausgeben mit Sitzplätzen, Größe und löschen Funktion
            foreach ($_SESSION['Sushibar']->getGroups() as $k => $groups) {
              echo '<tr>';
              echo '<th scope="row" class="text-center">'.$k.'</th>'; # ID
                echo '<td class="text-center">'. $groups->getgroupSize() .'</td>'; # Gruppengröße
                echo '<td class="text-center"><div class="d-flex justify-content-center flex-wrap">';

                foreach ($groups->getSeats() as $seat) {
                  echo '<div class="seat occupied mr-2 mb-2">'. $seat->getSeatID().'</div>'; 
                }

                echo '</button>';

                echo '</div></td>';
                echo '<td><form method="POST" action="index.php"><button type="submit" value="'.$k.'" class="btn btn-primary btn-block" name="groupLeaving">Gruppe geht</button></form></td>';
              echo '</tr>';
            }

        ?>
      </tbody>
    </table>
    <?php
      } // END else ( Gäste vorhanden )
    ?>
    </div>
  </div>

  <!--
    Leeren Button
  -->
  <div class="card mb-4 shadow-sm">
    <div class="card-body">
      <form method="POST" action="index.php"><button type="submit" class="btn btn-danger mb-2 btn-block" name="clearSeats">Alle Sitzplätze und Gruppen leeren</button></form>
      <form method="POST" action="index.php"><button type="submit" class="btn btn-danger btn-block" name="clearSession">Session löschen (nach anpassen der config z.B.)</button></form>
    </div>
  </div>

</div> <!-- END container -->
</body>
</html>

