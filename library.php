<?php
    if (!isset($_SESSION)) {
        session_start();
    }
?>

<?php

    $connection = new mysqli('localhost', 'root', '', 'hospital');

    $error_flag = 0;
    $result;
    if ($connection->connect_error) {
        die('connection failed: '.$connection->connect_error);
    }

    function secure($unsafe_data)
    {
        return htmlentities($unsafe_data);
    }

    function login($email_id_unsafe, $password_unsafe, $table = 'users')
    {
        global $connection;

        $email_id = secure($email_id_unsafe);
        $password = secure($password_unsafe);

        $sql = "SELECT COUNT(*) FROM $table WHERE email = '$email_id' AND password = '$password';";

        $result = $connection->query($sql);

        $num_rows = (int) $result->fetch_array()['0'];

        if ($num_rows > 1) {
            //send email to sysadmin that my site has been hacked
              return 0;
        } elseif ($num_rows == 0) {
            echo status('no-match');

            return 0;
        } else {
            echo "<div class='alert alert-success'> <strong>Well done!</strong> Logged In</div>";
            $_SESSION['username'] = $email_id;

            if ($table == 'admin') {
                $_SESSION['user-type'] = 'admin';
            }

            if ($table == 'users' || $table == 'doctors' || $table == 'clerks') {
                $sql = "SELECT fullname FROM $table WHERE email = '$email_id' AND password = '$password';";

                $result = $connection->query($sql);

                $fullname = $result->fetch_array()['fullname'];
                $_SESSION['fullname'] = $fullname;
                if ($table == 'users') {
                    $_SESSION['user-type'] = 'normal';
                } elseif ($table == 'clerks') {
                    $_SESSION['user-type'] = 'clerk';
                } else {
                    $_SESSION['user-type'] = 'doctor';
                }
            }

            return 1;
        }
    }

    function register($email_id_unsafe, $password_unsafe, $full_name_unsafe, $speciality_unsafe = 'doctor', $table = 'users')
    {
        global $connection,$error_flag;

        $email = secure($email_id_unsafe);
        $password = secure($password_unsafe);
        $speciality = secure($speciality_unsafe);
        $fullname = ucfirst(secure($full_name_unsafe));

        $sql;

        switch ($table) {
            case 'users':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname');";
                break;
            case 'doctors':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname','$speciality');";
                break;
            case 'clerks':
                $sql = "INSERT INTO $table VALUES ('$email', '$password', '$fullname');";
                break;
            default:
                // code...
                break;
        }

        if ($connection->query($sql) === true) {
            echo status('record-success');
            if ($table == 'users' && $error_flag == 0) {
                return login($email, $password);
            }
        } else {
            echo status('record-fail');
        }
    }

    function status($type, $data = 0)
    {
        $success = "<div class='alert alert-success'> <strong>Done!</strong>";
        $fail = "<div class='alert alert-warning'><strong>Sorry!</strong>";
        $end = '</div>';

        switch ($type) {
            case 'record-success':
                return "$success New record created successfully! $end";
                break;
            case 'record-fail':
                return "$fail New record creation failed. $end";
                break;
            case 'record-dup':
                return "$fail Duplicate record exists. $end";
                break;
            case 'no-match':
                return "$fail Record did not match. $end";
                break;
            case 'con-failed':
                return "$fail connection Failed! $end";
                break;
            case 'appointment-success':
                return "$success Your appointment is booked successfully! Your appointment no is $data $end";
                break;
            case 'appointment-fail':
                return "$fail Failed to book your appointment Failed! $end";
                break;
            case 'update-success':
                return "$success New record updated successfully! $end";
                break;
            case 'update-fail':
                return "$fail Failed to update data! $end";
                break;
            default:
                // code...
                break;
        }
    }

  function enter_patient_info($full_name_unsafe, $age_unsafe, $weight_unsafe, $phone_no_unsafe, $address_unsafe)
  {
      global $connection, $error_flag,$result;

      $full_name = ucfirst(secure($full_name_unsafe));
      $age = secure($age_unsafe);
      $weight = secure($weight_unsafe);
      $phone_no = secure($phone_no_unsafe);
      $address = secure($address_unsafe);

      $sql = "INSERT INTO `patient_info` VALUES (NULL, '$full_name', $age,$weight, '$phone_no','$address');";

      if ($connection->query($sql) === true) {
          echo status('record-success');

          return $connection->insert_id;
      } else {
          echo status('record-fail');

          return 0;
      }
  }

    function appointment_booking($patient_id_unsafe, $specialist_unsafe, $medical_condition_unsafe)
    {
        global $connection;
        $patient_id = secure($patient_id_unsafe);
        $specialist = secure($specialist_unsafe);
        $medical_condition = secure($medical_condition_unsafe);

        $sql = "INSERT INTO appointments VALUES (NULL, $patient_id, '$specialist', '$medical_condition', NULL, NULL, 'no')";

        if ($connection->query($sql) === true) {
            echo status('appointment-success', $connection->insert_id);
        } else {
            echo status('appointment-fail');
            echo 'Error: '.$sql.'<br>'.$connection->error;
        }
    }

    function update_appointment_info($appointment_no_unsafe, $column_name_unsafe, $data_unsafe)
    {
        global $connection;

        $sql;

        $appointment_no = (int) secure($appointment_no_unsafe);
        $column_name = secure($column_name_unsafe);
        $data = secure($data_unsafe);

        if ($column_name == 'payment_amount') {
            $data = (int) $data;
            $sql = "UPDATE `appointments` SET `payment_amount` = '$data', `case_closed` = 'no' WHERE appointment_no` = $appointment_no";
        } else {
            $sql = "UPDATE appointments SET $column_name = '$data' WHERE appointment_no = $appointment_no;";
        }
        echo $sql;
        if ($connection->query($sql) === true) {
            echo status('update-success');

            return 1;
        } else {
            echo status('update-fail');
            echo 'Error: '.$sql.'<br>'.$connection->error;

            return 0;
        }
    }

    function getPatientsFor($doctor = 'Dentist')
    {
        global $connection;

        return $connection->query("SELECT appointment_no, full_name, medical_condition FROM patient_info, appointments where speciality='$doctor' AND patient_info.patient_id = appointments.patient_id");
    }

    function getAllAppointments()
    {
        global $connection;

        return $connection->query('SELECT appointment_no, full_name,speciality, medical_condition FROM patient_info, appointments where patient_info.patient_id = appointments.patient_id');
    }

    function getAllPatientDetail($appointment_no)
    {
        global $connection;

        return $connection->query("SELECT appointment_no, full_name, dob, weight, phone_no, address, medical_condition FROM patient_info, appointments where appointment_no=$appointment_no AND patient_info.patient_id = appointments.patient_id;");
    }

    function get_table($purpose, $data)
    {
        global $connection;

        $sql;

        switch ($purpose) {
            case 'patient_information':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            case 'doctor-home':
                $sql = '';

                $result = $connection->query($sql);

                echo "<table border='1'>
				<tr>
				<th>appointment_no</th>
				<th>patient_name</th>
				<th>age</th>
				<th>appointment_time</th>
				<th>medical_condition</th>
				<th>option</th>
				</tr>";

                while ($row = $result->fetch_array()) {
                    echo '<tr>';
                    echo '<td>'.$row['appointment_no'].'</td>';
                    echo '<td>'.$row['full_name'].'</td>';
                    echo '<td>'.$row['age'].'</td>';
                    echo '<td>'.$row['appointment_time'].'</td>';
                    echo '<td>'.$row['medical_condition'].'</td>';
                    echo "<td> <button class='btn btn-primary'> Open Case</button> <button class='btn btn-primary'> Close Case</button> </td>";
                    echo '</tr>';
                }
                echo '</table>';
                break;
            case 'all':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            case 'patient_information':
                $sql = 'SELECT * FROM patient_info AND (SELECT )';
                break;
            default:
                // code...
                break;
        }
    }

    function appointment_status($appointment_no_unsafe)
    {
        global $connection;

        $appointment_no = secure($appointment_no_unsafe);
        $i = 0;

        $result = $connection->query("SELECT doctors_suggestion FROM appointments WHERE appointment_no=$appointment_no;");
        if ($result === false) {
            return 0;
        } else {
            ++$i;
        }

        $result = $connection->query('SELECT payment_amount FROM appointments WHERE appointment_no=appointment_no;');
        if ($result->num_rows == 1) {
            ++$i;
        }

        return $i;
    }

    function delete($table, $id_unsafe)
    {
        global $connection;

        $id = secure($id_unsafe);

        return $connection->query("DELETE FROM $table WHERE email='$id';");
    }

    function getListOfEmails($table)
    {
        global $connection;

        return $connection->query("SELECT email FROM $table;");
    }

    function noAccessForNormal()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'normal') {
                echo '<script type="text/javascript">window.location = "add_patient.php"</script>';
            }
        }
    }
    function noAccessForDoctor()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'doctor') {
                echo '<script type="text/javascript">window.location = "patient_info.php"</script>';
            }
        }
    }
    function noAccessForClerk()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'clerk') {
                echo '<script type="text/javascript">window.location = "all_appointments.php"</script>';
            }
        }
    }

    function noAccessForAdmin()
    {
        if (isset($_SESSION['user-type'])) {
            if ($_SESSION['user-type'] == 'admin') {
                echo '<script type="text/javascript">window.location = "admin_home.php"</script>';
            }
        }
    }

    function noAccessIfLoggedIn()
    {
        if (isset($_SESSION['user-type'])) {
            noAccessForNormal();
            noAccessForAdmin();
            noAccessForClerk();
            noAccessForDoctor();
        }
    }

    function noAccessIfNotLoggedIn()
    {
        if (!isset($_SESSION['user-type'])) {
            echo '<script type="text/javascript">window.location = "index.php"</script>';
        }
    }

?>
