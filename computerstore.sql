CREATE TABLE Motherboard(
	Motherboard Model Name	CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL
)

CREATE TABLE CPU(
	CPU Model Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Frequency			FLOAT		NOT NULL,
	Brand				CHAR(20)	NOT NULL,
	Core				INTEGER	NOT NULL,
	Cache				INTEGER	NOT NULL
)

CREATE TABLE RAM(
	RAM Model Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Frequency			FLOAT		NOT NULL,
	Brand				CHAR(20)	NOT NULL,
	Memory Types			CHAR(20)	NOT NULL,
	Size				INTEGER	NOT NULL
)

CREATE TABLE RAM_Memory(
	Memory Types		CHAR(20)	PRIMARY KEY,
	Frequency			FLOAT 	NOT NULL
)

CREATE TABLE RAM_Model(
RAM Model Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Brand				CHAR(20)	NOT NULL,
	Memory Types			CHAR(20)	NOT NULL,
	Size				INTEGER	NOT NULL
)

CREATE TABLE Cooling System(
	CS Model Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Brand				CHAR(20)	NOT NULL,
	Type				CHAR(20)	NOT NULL
)

CREATE TABLE Storage(
	Storage Model Name		CHAR(20)	PRIMARY KEY,
	Price 				FLOAT		NOT NULL,
	Brand				CHAR(20)	NOT NULL,
	Size				INTEGER	NOT NULL
)

CREATE TABLE HDD(
	Storage Model Name		CHAR(20)	PRIMARY KEY,
	RPM				INTEGER	NOT NULL,
	FOREIGN KEY (Storage Model Name)
REFERENCES Storage
ON DELETE CASCADE
			ON UPDATE CASCADE
)

CREATE TABLE SSD(
	Storage Model Name		 CHAR(20)	PRIMARY KEY,
	Interface			 CHAR(20)	NOT NULL,
	FOREIGN KEY (Storage Model Name)
REFERENCES Storage
ON DELETE CASCADE
			ON UPDATE CASCADE
)

CREATE TABLE Mounts_Storage_Motherboard(
	Storage Model Name 		 CHAR(20)
Motherboard Model Name	 CHAR(20)
	PRIMARY KEY(Storage Model Name, Motherboard Model Name),
	FOREIGN KEY(Storage Model Name) REFERENCES Storage
	FOREIGN KEY(Motherboard Model Name) REFERENCES Motherboard
		ON DELETE CASCADE
		ON UPDATE CASCADE
)

CREATE TABLE Controls_CPU_Motherboard(
	CPU Model Name 		 CHAR(20)
Motherboard Model Name	 CHAR(20)
	PRIMARY KEY(CPU Model Name, Motherboard Model Name),
	FOREIGN KEY(CPU Model Name) REFERENCES CPU
	FOREIGN KEY(Motherboard Model Name) REFERENCES Motherboard
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Inserts_RAM_Motherboard(
	RAM Model Name 		 CHAR(20)
Motherboard Model Name	 CHAR(20)
	PRIMARY KEY(RAM Model Name, Motherboard Model Name),
	FOREIGN KEY(RAM Model Name) REFERENCES RAM
	FOREIGN KEY(Motherboard Model Name) REFERENCES Motherboard
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Cools_Cooling System_Motherboard(
	CS Model Name 		 CHAR(20)
Motherboard Model Name	 CHAR(20)
	PRIMARY KEY(CS Model Name, Motherboard Model Name),
	FOREIGN KEY(CS Model Name) REFERENCES Cooling System
	FOREIGN KEY(Motherboard Model Name) REFERENCES Motherboard
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Connects_Motherboard_Computer(
Motherboard Model Name	 CHAR(20)
Computer Model Name	 CHAR(20),
Operating System		 CHAR(20),		NOT NULL,
	Chassis Brand		 CHAR(20),		NOT NULL,
	Size				 CHAR(20)		NOT NULL,
	Price				 FLOAT		NOT NULL,
	PRIMARY KEY(Motherboard Model Name, Computer Model Name),
	FOREIGN KEY(Motherboard Model Name) REFERENCES Motherboard
		ON DELETE NO ACTION
ON UPDATE CASCADE
FOREIGN KEY(Computer Model Name) REFERENCES Computer
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Customer / Computer

CREATE TABLE Customer(
	Customer_ID		INTEGER	PRIMARY KEY,
	Name			CHAR(50)	NOT NULL,
	Email			CHAR(50)	NOT NULL,
	Phone           Number	INTEGER	NOT NULL
)

-- Note: Changed Milliamps attribute to Capacity
CREATE TABLE Battery(
	Battery_Model_Name	CHAR(50)	PRIMARY KEY,
	Brand			    CHAR(20),	NOT NULL,
	Capacity		    FLOAT		NOT NULL,
	Price			    FLOAT		NOT NULL
)

CREATE TABLE GPU(
	GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
	Brand 			CHAR(50),	NOT NULL,
	CUDA_core		INTEGER	NOT NULL,
	Frequency		FLOAT		NOT NULL,
	Price			FLOAT		NOT NULL
)

CREATE TABLE GPU_CUDACore(
	CUDA_core			INTEGER	PRIMARY KEY,
	Frequency			FLOAT 	NOT NULL
)


CREATE TABLE GPU_Model(
	GPU_Model_Name 	CHAR(50)	PRIMARY KEY,
	Brand 			CHAR(50),	NOT NULL,
	CUDA_core		INTEGER	NOT NULL,
	Price			FLOAT		NOT NULL
)

-- Note: There is a total participation from GPU to Mounts which means GPU Model Name cannot be null but since GPU Model Name is a PK, NOT NULL is not required.
CREATE TABLE Mounts_GPU_Computer(
	Computer_Model_Name	    CHAR(50),
	GPU_Model_Name		    CHAR(50),
	PRIMARY KEY(Computer_Model_Name, GPU_Model_Name),
	FOREIGN KEY(Computer_Model_Name) REFERENCES
    Connects_Motherboard_Computer
			ON DELETE NO ACTION
			ON UPDATE CASCADE
	FOREIGN KEY(GPU_Model_Name) REFERENCES GPU
    ON DELETE NO ACTION
			ON UPDATE CASCADE
)

-- Note: There is a total participation from Customer to Purchases which means CustomerID cannot be null but since CustomerID is a PK, NOT NULL is not required.
CREATE TABLE Purchases_Computer_Customer(
	Computer Model_Name	CHAR(50),
	Customer ID			CHAR(50),
	OrderID			    INTEGER,
	PRIMARY KEY(Computer_Model_Name, Customer_ID, OrderID),
	FORIEGN KEY(Computer_Model Name) REFERENCES
    Connects_Motherboard_Computer,
			ON DELETE NO ACTION
			ON UPDATE CASCADE
	FOREIGN KEY(Customer_ID) REFERENCES Customer
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Note: There is a total participation from Customer to Purchases which means CustomerID cannot be null but since CustomerID is a PK, NOT NULL is not required.

CREATE TABLE Purchases_Accessory_Customer(
	Accessories_Model_Name	CHAR(50),
	Customer_ID			    CHAR(50),
	OrderID			        INTEGER,
	PRIMARY KEY(Accessories_Model_Name, Customer_ID, OrderID),
	FORIEGN KEY(Accessories_Model_Name) REFERENCES Accessory
		ON DELETE NO ACTION
		ON UPDATE CASCADE
	FOREIGN KEY(Customer_ID) REFERENCES Customer
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

CREATE TABLE Powers_Battery_Computer(
	Computer_Model_Name	    CHAR(50),
	Battery_Model_Name		CHAR(50),
	PRIMARY KEY(Computer_Model_Name, Battery_Model_Name),
	FORIEGN KEY(Computer_Model_Name) REFERENCES
    Connects_Motherboard_Computer
			ON DELETE NO ACTION
			ON UPDATE CASCADE
    FORIEGN KEY(Battery_Model_Name) REFERENCES Battery
		ON DELETE NO ACTION
		ON UPDATE CASCADE
)

-- Accessory

CREATE TABLE Accessory(
	Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
	Brand				    CHAR(20)	NOT NULL,
	Price 				    FLOAT		NOT NULL
)

CREATE TABLE Monitor(
	Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
	Refresh_Rate			CHAR(20)	NOT NULL,
	Resolution			    CHAR(20)	NOT NULL,
	FOREIGN KEY (Accessory_Model_Name)
    REFERENCES Accessory
    ON DELETE CASCADE
    ON UPDATE CASCADE
)

CREATE TABLE Keyboard(
	Accessory_Model_Name	CHAR(50)	PRIMARY KEY,
	Connection_Type		    CHAR(20)	NOT NULL,
	Power_Source			CHAR(20)	NOT NULL,
	Size				    CHAR(20)	NOT NULL,
	FOREIGN KEY (Accessory Model Name)
    REFERENCES Accessory
    ON DELETE CASCADE
    ON UPDATE CASCADE
)

Create Table Mouse(
	Accessory_Model_Name    CHAR(50)	PRIMARY KEY,
	Sensor_Type			    CHAR(20)	NOT NULL,
	Connection_Type		CHAR(20)	NOT NULL,
	Power_Source			CHAR(20)	NOT NULL,
	FOREIGN KEY (Accessory_Model_Name)
    REFERENCES Accessory
    ON DELETE CASCADE
	ON UPDATE CASCADE
)
