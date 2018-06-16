<?php
    if (!isset($_SESSION)) {
        session_start();
    }
?>
<!DOCTYPE html>
<html lang="en">
<?php
  $beginning = '<div class="container"><nav class="navbar  navbar-default "><div class="navbar-header">
      <a class="navbar-brand">Navigation Bar </a>
    </div><ul class="nav navbar-nav justified">';
  $frontLink = '<li class="nav-item"> <a class="" href="';
  $endLink = '</a></li>';

  if (isset($_SESSION['user-type'])) {
      echo $beginning;

      switch ($_SESSION['user-type']) {
      case 'doctor':

        echo $frontLink.'add_patient.php"> Add Patient '.$endLink;
        echo $frontLink.'patient_info.php"> Upcomming Appointments '.$endLink;
        break;
      case 'clerk':
        echo $frontLink.'add_patient.php"> Add Patient '.$endLink;
        echo $frontLink.'patient_info.php"> All Appointments '.$endLink;
        break;
      default:
        // code...
        break;
    }
      echo '</ul> </nav></div>';
  }

?>
