DELIMITER $$
CREATE FUNCTION setPrecision(p_inputNumber decimal(30,15),p_precision int(20))
  RETURNS TEXT
  DETERMINISTIC
BEGIN
  DECLARE inputNumberAsString TEXT;
  DECLARE op_result TEXT;

  SET inputNumberAsString = CAST(p_inputNumber AS char);

  IF(LOCATE('.',inputNumberAsString) = 0) THEN
    SET inputNumberAsString = CONCAT(inputNumberAsString,'.000000000000000');
  ELSE
    SET inputNumberAsString = CONCAT(inputNumberAsString,'000000000000000');
  END IF;

  SET op_result = SUBSTRING(inputNumberAsString, 1, LOCATE('.',inputNumberAsString) + p_precision);
  
  return op_result;
END$$

DELIMITER ;
/*
DROP FUNCTION IF EXISTS setPrecision;
SELECT `(5,2);
*/
