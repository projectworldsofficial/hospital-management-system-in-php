<?php
    if(!isset($_SESSION)) 
    { 
        session_start(); 
    } 

?>
<link href="bootstrap.min.css" rel="stylesheet">

<?php 
  include("header.php");
  include("library.php");

  noAccessForNormal();
  noAccessForAdmin();
  noAccessForDoctor();

  include('nav-bar.php');
?>
<div class="container">
<h2>Update Payment Information</h2>
<p>Enter information below</p>
<table class="table table-striped">
<?php



  if(isset($_POST['payment'])){
      $i = update_appointment_info($_POST['appointment_no'], 'payment_amount', $_POST['payment']);
      if($i==1){
        echo "<script type='text/javascript'>window.location = 'all_appointments.php'</script>";
      }
  }

  if(isset($_GET['appointment_no'])){
    $appointment_no = $_GET['appointment_no'];
    $result = getAllPatientDetail($appointment_no);

	$row = $result->fetch_array();
  
    $link = "<tr><th>";
    $mid = "</th><td>";
    $endingTag = "</td></tr>";
    echo "<tr>";   // appointment_no, full_name, dob, weight, phone_no, address, blood_group, medical_condition

    echo "$link Appointment No $mid". $row['appointment_no'] . "$endingTag";
    echo "$link Full Name $mid" . $row['full_name'] . "$endingTag";
    echo "$link Age (in years) $mid" . $row['dob'] . "$endingTag";

    echo "$link Weight $mid" . $row['weight'] . "$endingTag";

    echo "$link Phone No $mid" . $row['phone_no'] . "$endingTag";

    echo "$link Address $mid" . $row['address'] . "$endingTag";

    echo "$link Medical Condition $mid" . $row['medical_condition'] . "$endingTag";


    echo "$link Payment $mid" . "<form action='payment.php' method='post'>


          <select required value=1 class ='form-control' name='payment' style='width: 500;'>
                <option value='admin' class='option'>200</option>
                <option value='clerks' class='option'>500</option>
                <option value='doctors' class='option'>900</option>
          </select>
<input type='number' style='visibility: hidden; width; 1px;' name=\"appointment_no\" value =" . $appointment_no . ">

    <input type='submit' class='btn btn-primary' action='payment.php'></form>" . "$endingTag";


    echo "</tr>";
  
  }
?>
</table>

</div>


<?php include("footer.php"); ?>


