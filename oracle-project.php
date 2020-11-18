<!--Test Oracle file for UBC CPSC304 2018 Winter Term 1
  Created by Jiemin Zhang
  Modified by Simona Radu
  Modified by Jessica Wong (2018-06-22)
  This file shows the very basics of how to execute PHP commands
  on Oracle.  
  Specifically, it will drop a table, create a table, insert values
  update values, and then query for values
 
  IF YOU HAVE A TABLE CALLED "demoTable" IT WILL BE DESTROYED

  The script assumes you already have a server set up
  All OCI commands are commands to the Oracle libraries
  To get the file to work, you must place it somewhere where your
  Apache server can run it, and you must rename it to have a ".php"
  extension.  You must also change the username and password on the 
  OCILogon below to be your ORACLE username and password -->

<html>
    <head>
        <title>CPSC 304 PHP/Oracle Demonstration</title>
    </head>

    <body>
        <h2>Reset</h2>
        <p>If you wish to reset the table press on the reset button. If this is the first time you're running this page, you MUST use reset</p>

        <form method="POST" action="oracle-tut.php">
            <!-- if you want another page to load after the button is clicked, you have to specify that page in the action parameter -->
            <input type="hidden" id="resetTablesRequest" name="resetTablesRequest">
            <p><input type="submit" value="Reset" name="reset"></p>
        </form>

        <hr />

        <h2>Insert Values into DemoTable</h2>
        <form method="POST" action="oracle-tut.php"> <!--refresh page when submitted-->
            <input type="hidden" id="insertQueryRequest" name="insertQueryRequest">
            Number: <input type="text" name="insNo"> <br /><br />
            Name: <input type="text" name="insName"> <br /><br />

            <input type="submit" value="Insert" name="insertSubmit"></p>
        </form>

        <hr />

        <h2>Update Name in DemoTable</h2>
        <p>The values are case sensitive and if you enter in the wrong case, the update statement will not do anything.</p>

        <form method="POST" action="oracle-tut.php"> <!--refresh page when submitted-->
            <input type="hidden" id="updateQueryRequest" name="updateQueryRequest">
            Old Name: <input type="text" name="oldName"> <br /><br />
            New Name: <input type="text" name="newName"> <br /><br />

            <input type="submit" value="Update" name="updateSubmit"></p>
        </form>

        <hr />

        <h2>Count the Tuples in DemoTable</h2>
        <form method="GET" action="oracle-tut.php"> <!--refresh page when submitted-->
            <input type="hidden" id="countTupleRequest" name="countTupleRequest">
            <input type="submit" name="countTuples"></p>
        </form>

        <?php
		//this tells the system that it's no longer just parsing html; it's now parsing PHP

        $success = True; //keep track of errors so it redirects the page only if there are no errors
        $db_conn = NULL; // edit the login credentials in connectToDB()
        $show_debug_alert_messages = False; // set to True if you want alerts to show you which methods are being triggered (see how it is used in debugAlertMessage())

        function debugAlertMessage($message) {
            global $show_debug_alert_messages;

            if ($show_debug_alert_messages) {
                echo "<script type='text/javascript'>alert('" . $message . "');</script>";
            }
        }

        function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
            //echo "<br>running ".$cmdstr."<br>";
            global $db_conn, $success;

            $statement = OCIParse($db_conn, $cmdstr); 
            //There are a set of comments at the end of the file that describe some of the OCI specific functions and how they work

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn); // For OCIParse errors pass the connection handle
                echo htmlentities($e['message']);
                $success = False;
            }

            $r = OCIExecute($statement, OCI_DEFAULT);
            if (!$r) {
                echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                $e = oci_error($statement); // For OCIExecute errors pass the statementhandle
                echo htmlentities($e['message']);
                $success = False;
            }

			return $statement;
		}

        function executeBoundSQL($cmdstr, $list) {
            /* Sometimes the same statement will be executed several times with different values for the variables involved in the query.
		In this case you don't need to create the statement several times. Bound variables cause a statement to only be
		parsed once and you can reuse the statement. This is also very useful in protecting against SQL injection. 
		See the sample code below for how this function is used */

			global $db_conn, $success;
			$statement = OCIParse($db_conn, $cmdstr);

            if (!$statement) {
                echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
                $e = OCI_Error($db_conn);
                echo htmlentities($e['message']);
                $success = False;
            }

            foreach ($list as $tuple) {
                foreach ($tuple as $bind => $val) {
                    //echo $val;
                    //echo "<br>".$bind."<br>";
                    OCIBindByName($statement, $bind, $val);
                    unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype
				}

                $r = OCIExecute($statement, OCI_DEFAULT);
                if (!$r) {
                    echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
                    $e = OCI_Error($statement); // For OCIExecute errors, pass the statementhandle
                    echo htmlentities($e['message']);
                    echo "<br>";
                    $success = False;
                }
            }
        }

        function printResult($result) { //prints results from a select statement
            echo "<br>Retrieved data from table demoTable:<br>";
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th></tr>";

            while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
                echo "<tr><td>" . $row["NID"] . "</td><td>" . $row["NAME"] . "</td></tr>"; //or just use "echo $row[0]" 
            }

            echo "</table>";
        }

        function connectToDB() {
            global $db_conn;

            // Your username is ora_(CWL_ID) and the password is a(student number). For example, 
			// ora_platypus is the username and a12345678 is the password.
            $db_conn = OCILogon("ora_davidw7", "a14496160", "dbhost.students.cs.ubc.ca:1522/stu");

            if ($db_conn) {
                debugAlertMessage("Database is Connected");
                return true;
            } else {
                debugAlertMessage("Cannot connect to Database");
                $e = OCI_Error(); // For OCILogon errors pass no handle
                echo htmlentities($e['message']);
                return false;
            }
        }

        function disconnectFromDB() {
            global $db_conn;

            debugAlertMessage("Disconnect from Database");
            OCILogoff($db_conn);
        }

        function handleUpdateRequest() {
            global $db_conn;

            $old_name = $_POST['oldName'];
            $new_name = $_POST['newName'];

            // you need the wrap the old name and new name values with single quotations
            executePlainSQL("UPDATE demoTable SET name='" . $new_name . "' WHERE name='" . $old_name . "'");
            OCICommit($db_conn);
        }

        function handleResetRequest() {
            global $db_conn;
            // Drop old table
            executePlainSQL("DROP TABLE demoTable");
            // TODO: Drop all the table that was created

            // Create new table
            echo "<br> creating new table <br>";
             // Computer and Motherboard DDLs
             executePlainSQL("CREATE TABLE Motherboard(
                Motherboard_Model_Name CHAR(20) PRIMARY KEY, 
                Price FLOAT NOT NULL
            )");
            executePlainSQL("CREATE TABLE CPU(
	            CPU_Model_Name		CHAR(20)	PRIMARY KEY,
	            Price 				FLOAT		NOT NULL,
	            Frequency			FLOAT		NOT NULL,
	            Core				INTEGER	    NOT NULL
            )");
            executePlainSQL("CREATE TABLE RAM(
                RAM_Model_Name		CHAR(20)	PRIMARY KEY,
                Price 				FLOAT		NOT NULL,
                Frequency			FLOAT		NOT NULL,
                Memory_Types		CHAR(20)	NOT NULL,
                Size				INTEGER	    NOT NULL
            )");
            executePlainSQL("CREATE TABLE RAM_Memory(
                Memory_Types		CHAR(20)	PRIMARY KEY,
                Frequency			FLOAT 	    NOT NULL
            )");
            executePlainSQL("CREATE TABLE RAM_Model(
                RAM_Model_Name		CHAR(20)	PRIMARY KEY,
                Price 				FLOAT		NOT NULL,
                Memory_Types		CHAR(20)	NOT NULL,
                Size				INTEGER	    NOT NULL,
                FOREIGN KEY (Memory_Types) REFERENCES RAM_Memory
                    ON DELETE CASCADE
            )");
            executePlainSQL("CREATE TABLE Storage(
                Storage_Model_Name	CHAR(20)	PRIMARY KEY,
                Price 				FLOAT		NOT NULL,
                Size				INTEGER	NOT NULL
            )");
            executePlainSQL("CREATE TABLE HDD(
                Storage_Model_Name		CHAR(20)	PRIMARY KEY,
                RPM				        INTEGER 	NOT NULL,
                FOREIGN KEY (Storage_Model_Name) REFERENCES Storage
                    ON DELETE CASCADE
            )");
            executePlainSQL("CREATE TABLE SSD(
                Storage_Model_Name		 CHAR(20)	PRIMARY KEY,
                Interface			     CHAR(20)	NOT NULL,
                FOREIGN KEY (Storage_Model_Name) REFERENCES Storage
                    ON DELETE CASCADE
            )");
            executePlainSQL("CREATE TABLE Mounts_Storage_Motherboard(
                Storage_Model_Name 		 CHAR(20),
                Motherboard_Model_Name	 CHAR(20),
                PRIMARY KEY(Storage_Model_Name, Motherboard_Model_Name),
                FOREIGN KEY(Storage_Model_Name) REFERENCES Storage,
                FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
                    ON DELETE CASCADE
            )");

            executePlainSQL("CREATE TABLE Controls_CPU_Motherboard(
                CPU_Model_Name 		     CHAR(20),
                Motherboard_Model_Name	 CHAR(20),
                PRIMARY KEY(CPU_Model_Name, Motherboard_Model_Name),
                FOREIGN KEY(CPU_Model_Name) REFERENCES CPU,
                FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
                    ON DELETE NO ACTION
            )");
            executePlainSQL("CREATE TABLE Inserts_RAM_Motherboard(
                RAM_Model_Name 		     CHAR(20),
                Motherboard_Model_Name	 CHAR(20),
                PRIMARY KEY(RAM_Model_Name, Motherboard_Model_Name),
                FOREIGN KEY(RAM_Model_Name) REFERENCES RAM,
                FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
                    ON DELETE NO ACTION
            )");
            executePlainSQL("CREATE TABLE Connects_Motherboard_Computer(
                Motherboard_Model_Name	 CHAR(20),
                Computer_Model_Name	     CHAR(20),
                Operating_System		 CHAR(20)		NOT NULL,
                Size				     CHAR(20)		NOT NULL,
                Price				     FLOAT		    NOT NULL,
                PRIMARY KEY(Motherboard_Model_Name, Computer_Model_Name),
                FOREIGN KEY(Motherboard_Model_Name) REFERENCES Motherboard
                    ON DELETE NO ACTION,
                FOREIGN KEY(Computer_Model_Name) REFERENCES Computer
                    ON DELETE NO ACTION
            )");
            
            // Customer SQL DDLs
            executePlainSQL("CREATE TABLE Customer(
                Customer_ID		INTEGER	    PRIMARY KEY,
                Full_Name		CHAR(50)	NOT NULL,
                Email			CHAR(50)	NOT NULL
            )");
            executePlainSQL("CREATE TABLE GPU(
                GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
                CUDA_core		INTEGER	NOT NULL,
                Frequency		FLOAT		NOT NULL,
                Price			FLOAT		NOT NULL
            )");
            executePlainSQL("CREATE TABLE GPU_CUDACore(
                CUDA_core			INTEGER	PRIMARY KEY,
                Frequency			FLOAT 	NOT NULL
            )");
            executePlainSQL("CREATE TABLE GPU_Model(
                GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
                CUDA_core		INTEGER	NOT NULL,
                Price			FLOAT		NOT NULL,
                FOREIGN KEY (CUDA_core) REFERENCES GPU_CUDACore
                    ON DELETE CASCADE
            )");
            executePlainSQL("CREATE TABLE Mounts_GPU_Computer(
                Computer_Model_Name	    CHAR(50),
                GPU_Model_Name		    CHAR(50),
                PRIMARY KEY(Computer_Model_Name, GPU_Model_Name),
                FOREIGN KEY(Computer_Model_Name) REFERENCES Connects_Motherboard_Computer
                    ON DELETE NO ACTION,
                FOREIGN KEY(GPU_Model_Name) REFERENCES GPU
                    ON DELETE NO ACTION
            )");
            executePlainSQL("CREATE TABLE Purchases_Computer_Customer(
                Computer_Model_Name	CHAR(50),
                Customer_ID			CHAR(50),
                OrderID			    INTEGER,
                PRIMARY KEY(Computer_Model_Name, Customer_ID, OrderID),
                FOREIGN KEY(Computer_Model_Name) REFERENCES Connects_Motherboard_Computer
                    ON DELETE NO ACTION,
                FOREIGN KEY(Customer_ID) REFERENCES Customer
                    ON DELETE NO ACTION
            )");
            executePlainSQL("CREATE TABLE Purchases_Accessory_Customer(
                Accessories_Model_Name	CHAR(50),
                Customer_ID			    CHAR(50),
                OrderID			        INTEGER,
                PRIMARY KEY(Accessories_Model_Name, Customer_ID, OrderID),
                FOREIGN KEY(Accessories_Model_Name) REFERENCES Accessory
                    ON DELETE NO ACTION,
                FOREIGN KEY(Customer_ID) REFERENCES Customer
                    ON DELETE NO ACTION
            )");
            
            
            // Accessories DDLs
            executePlainSQL("CREATE TABLE Accessory(
                Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
                Price 				    FLOAT		NOT NULL
            )");

            executePlainSQL("CREATE TABLE Monitor(
                Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
                Refresh_Rate			CHAR(20)	NOT NULL,
                Resolution			    CHAR(20)	NOT NULL,
                FOREIGN KEY (Accessory_Model_Name) REFERENCES Accessory
                    ON DELETE CASCADE
            )");

            executePlainSQL("CREATE TABLE Mouse(
                Accessory_Model_Name    CHAR(50)	PRIMARY KEY,
                Sensor_Type			    CHAR(20)	NOT NULL,
                Connection_Type		    CHAR(20)	NOT NULL,
                FOREIGN KEY (Accessory_Model_Name) REFERENCES Accessory
                    ON DELETE CASCADE
            )");
            
            //TODO: Remember FK must refer to a existing value in the parent table

            // Inserting tuples to computer / motherboard
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus ROG Strix B450-F', 175)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus ROG Strix Z490-E', 384)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('Asus Prime Z390-A', 228)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('MSI Creator TRX40 Motherboard', 945)");
            executePlainSQL("INSERT INTO Motherboard VALUES ('GIGABYTE Z490 AORUS Master', 516)");

            executePlainSQL("INSERT INTO CPU VALUES ('CPU_Model_Name', Price, Frequency, Core)");
            
            executePlainSQL("INSERT INTO RAM VALUES ('RAM_Model_Name', Price, Frequency, Memory_Types, Size)");
            
            // Inserting tuples to customer table
            executePlainSQL("INSERT INTO Customer VALUES (1,'Bob', 'bob@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES (2,'Alex', 'alex@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES (3,'Charlie', 'charlie@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES (4,'Mary', 'mary@gmail.com')");
            executePlainSQL("INSERT INTO Customer VALUES (5,'Sara', 'sara@gmail.com')");

            // Inserting tuples to gpu table
            executePlainSQL("INSERT INTO GPU VALUES ('Asus GeForceGTX 1080 Ti 11GB STRIX',3584, 'Sara', 'sara@gmail.com')");

            // Inserting tuples to accessory
            executePlainSQL("INSERT INTO Accessory VALUES ('Microsoft Wireless Mobile Microsoft 15.95 Mouse 1850 -Black- U7Z-00002', 'Microsoft', '15.95')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Razer DeathAdder Essential Gaming Mouse: 6400 DPI Optical Sensor - 5 Programmable Buttons - Rubber Side Grips - Classic Black', 'Razer', '39.99')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Pro Display XDR Standard Glass', 'Apple', '6499')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Pro Display XDR Nano-texture glass', 'Apple', '7499')");
            executePlainSQL("INSERT INTO Accessory VALUES ('Apple Magic Keyboard', 'Apple, '99')");

            // Inserting tuples to Monitor
            executePlainSQL("INSERT INTO Monitor VALUES ('Dell 27 Inch 4k Monitor 2020', '120 Hz', '4k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Apple Pro Display XDR Standard Glass', '60 Hz', '6k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Apple Pro Display XDR Nano-texture glass', '60 Hz', '6k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Samsung Ultra 8k Monitor 2020', '120 Hz', '8k')");
            executePlainSQL("INSERT INTO Monitor VALUES ('Asus 4k Pro Monitor', '120 Hz', '4k')");

            // Inserting tuples to Mouse
            executePlainSQL("INSERT INTO Mouse VALUES ('Microsoft Wireless Mobile Mouse 1850 - Black - U7Z-00002', 'Laser', 'USB Wireless')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Razer DeathAdder Essential Gaming Mouse: 6400 DPI Optical Sensor - 5 Programmable Buttons - Rubber Side Grips - Classic Black', 'Optical', 'Wired')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Apple Magic Mouse', 'Optical', 'Wireless')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Logitech Gaming Mouse', 'Laser', 'Wired')");
            executePlainSQL("INSERT INTO Mouse VALUES ('Asus Ultimate Gaming Mice', 'Optical', 'USB Wireless')");

            OCICommit($db_conn);
        }

        function handleInsertRequest() {
            global $db_conn;

            //Getting the values from user and insert data into the table
            $tuple = array (
                ":bind1" => $_POST['insNo'],
                ":bind2" => $_POST['insName']
            );

            $alltuples = array (
                $tuple
            );

            executeBoundSQL("insert into demoTable values (:bind1, :bind2)", $alltuples);
            OCICommit($db_conn);
        }

        function handleCountRequest() {
            global $db_conn;

            $result = executePlainSQL("SELECT Count(*) FROM demoTable");

            if (($row = oci_fetch_row($result)) != false) {
                echo "<br> The number of tuples in demoTable: " . $row[0] . "<br>";
            }
        }

        // HANDLE ALL POST ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handlePOSTRequest() {
            if (connectToDB()) {
                if (array_key_exists('resetTablesRequest', $_POST)) {
                    handleResetRequest();
                } else if (array_key_exists('updateQueryRequest', $_POST)) {
                    handleUpdateRequest();
                } else if (array_key_exists('insertQueryRequest', $_POST)) {
                    handleInsertRequest();
                }

                disconnectFromDB();
            }
        }

        // HANDLE ALL GET ROUTES
	// A better coding practice is to have one method that reroutes your requests accordingly. It will make it easier to add/remove functionality.
        function handleGETRequest() {
            if (connectToDB()) {
                if (array_key_exists('countTuples', $_GET)) {
                    handleCountRequest();
                }

                disconnectFromDB();
            }
        }

		if (isset($_POST['reset']) || isset($_POST['updateSubmit']) || isset($_POST['insertSubmit'])) {
            handlePOSTRequest();
        } else if (isset($_GET['countTupleRequest'])) {
            handleGETRequest();
        }
		?>
	</body>
</html>

