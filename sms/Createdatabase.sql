CREATE OR REPLACE VIEW flat_search_results AS
SELECT h.house_name, f.*, u.name AS owner_name, u.contact_number AS owner_contact
FROM flat f
INNER JOIN house h ON f.house_id = h.house_id
INNER JOIN users u ON h.owner_id = u.id
WHERE f.renter_id IS NULL;

CREATE TABLE users (
  id NUMBER(11) PRIMARY KEY,
  name VARCHAR2(255) NOT NULL,
  email VARCHAR2(255) NOT NULL,
  password VARCHAR2(255) NOT NULL,
  role VARCHAR2(10) NOT NULL,
  contact_number number(11),
  
  created_at TIMESTAMP DEFAULT SYSTIMESTAMP
);



CREATE TABLE house (
  house_id NUMBER PRIMARY KEY,
  house_name VARCHAR2(255),
  parking VARCHAR2(255),
  service_charge NUMBER(10, 2),
  house_type VARCHAR2(255),
    owner_id NUMBER,

  FOREIGN KEY (owner_id) REFERENCES users(id)

);


CREATE TABLE flat (
  flat_no NUMBER PRIMARY KEY,
  floor_no NUMBER,
  flat_size NUMBER,
  furnish_type VARCHAR2(50),
  flat_rent NUMBER(10, 2),
  house_id NUMBER,
  renter_id NUMBER ,
  room_no NUMBER(1),
  owner_id NUMBER,

  FOREIGN KEY (renter_id) REFERENCES users(id),
  FOREIGN KEY (house_id) REFERENCES house(house_id),
  FOREIGN KEY (owner_id) REFERENCES users(id)

);


CREATE TABLE Forum (
  id NUMBER PRIMARY KEY,
  Subject VARCHAR2(255),
  details VARCHAR2(255),
  Response VARCHAR2(255),
  status VARCHAR2(255),
  renter_id NUMBER,
  complain_time TIMESTAMP DEFAULT SYSTIMESTAMP,
  owner_id NUMBER,
  FOREIGN KEY (owner_id) REFERENCES users(id),
  FOREIGN KEY (renter_id) REFERENCES users(id)
);


CREATE TABLE RENTREQUEST(
    house_id NUMBER,
    flat_no NUMBER,
    renter_id NUMBER,
    owner_id NUMBER,
    FOREIGN KEY (renter_id) REFERENCES users(id),
    FOREIGN KEY (owner_id) REFERENCES users(id),
    FOREIGN KEY (house_id) REFERENCES house(house_id),
    FOREIGN KEY (flat_no) REFERENCES flat(flat_no)
);

CREATE TABLE GUARD (
  Guard_ID NUMBER PRIMARY KEY,
  District VARCHAR2(255),
  Name VARCHAR2(255),
  Contact_info VARCHAR2(255),
  Gender VARCHAR2(255),
  House_ID NUMBER,
  FOREIGN KEY (House_ID) REFERENCES HOUSE(house_id)
);




-- Create table `bills`
CREATE TABLE bills (
  id NUMBER(11) PRIMARY KEY,
  flat_id NUMBER(11) NOT NULL,
  amount NUMBER(10, 2) NOT NULL,
  month VARCHAR2(30) NOT NULL,
  paid_date DATE DEFAULT SYSDATE,
  payment_method VARCHAR2(255) DEFAULT NULL,
  paid_amount NUMBER(10, 2) DEFAULT NULL,
  reference VARCHAR2(10) DEFAULT NULL,
  bill_status VARCHAR2(10) DEFAULT 'unpaid',
  FOREIGN KEY (flat_id) REFERENCES flat(flat_no)
);

CREATE OR REPLACE TRIGGER update_owner_id_trigger
BEFORE INSERT ON Forum
FOR EACH ROW
DECLARE
    l_owner_id NUMBER;
BEGIN
    SELECT owner_id INTO l_owner_id
    FROM Flat
    WHERE renter_id = :new.renter_id;

    :new.owner_id := l_owner_id;
EXCEPTION
    WHEN NO_DATA_FOUND THEN
        -- Handle the case when no matching data is found in the Flat table
        :new.owner_id := NULL; -- Set owner_id to NULL or take appropriate action
END;
/

CREATE OR REPLACE VIEW admin_dashboard_view AS
SELECT
    (SELECT COUNT(*) FROM users WHERE role = 'admin') AS total_owners,
    (SELECT COUNT(*) FROM users WHERE role = 'user') AS total_renters,
    (SELECT COUNT(*) FROM house) AS total_houses,
    (SELECT COUNT(*) FROM flat) AS total_flats,
    (SELECT SUM(amount) FROM bills WHERE paid_amount IS NOT NULL) AS total_amount_paid,
    (SELECT COUNT(*) FROM forum WHERE status = 'Solved') AS total_problems_solved,
    (SELECT COUNT(*) FROM forum WHERE status = 'Pending') AS total_problems_pending
FROM dual;



INSERT INTO USERS (ID, NAME, EMAIL, PASSWORD, ROLE, CREATED_AT, CONTACT_NUMBER)
VALUES (100, 'SuperAdmin', 'superadmin@sms.com', 'password', 'superadmin', SYSTIMESTAMP, '1234567890');



CREATE OR REPLACE TRIGGER CHECK_BILL_GENERATION
BEFORE INSERT ON bills
FOR EACH ROW
DECLARE
    bill_count NUMBER;
BEGIN
    SELECT COUNT(*) INTO bill_count
    FROM bills
    WHERE month = :new.month;

    IF bill_count > 0 THEN
        RAISE_APPLICATION_ERROR(-20001, 'Bills for ' || :new.month || ' are already generated!');
    END IF;
END;
/
