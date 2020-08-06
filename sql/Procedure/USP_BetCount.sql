DELIMITER $$
CREATE PROCEDURE `USP_BetCount` (IN p_GameID bigint UNSIGNED)
BEGIN

  DROP TEMPORARY TABLE IF EXISTS betCountTemp;
  CREATE TEMPORARY TABLE betCountTemp
  SELECT SUM(bet.betAmount) betAmounts,COUNT(1) betCounts,ru.name
  FROM game gm
    JOIN betting bet ON bet.gameID = gm.PID
    JOIN rule ru ON ru.PID = bet.ruleID
  WHERE gm.PID = p_GameID
  GROUP BY bet.ruleID;

  DROP TEMPORARY TABLE IF EXISTS betCount;
  CREATE TEMPORARY TABLE betCount
  SELECT IFNULL(SUM(betAmounts),0) betAmounts,IFNULL(SUM(betCounts),0) betCounts,'FD_NUMBER' name FROM betCountTemp WHERE name REGEXP '^FD_[0-9]+$';

  INSERT INTO betCount
  SELECT IFNULL(SUM(betAmounts),0) betAmounts,IFNULL(SUM(betCounts),0) betCounts,'LD_NUMBER' name FROM betCountTemp WHERE name REGEXP '^LD_[0-9]+$';

  INSERT INTO betCount
  SELECT IFNULL(SUM(betAmounts),0) betAmounts,IFNULL(SUM(betCounts),0) betCounts,'TD_NUMBER' name FROM betCountTemp WHERE name REGEXP '^TD_[0-9]+$';

  INSERT INTO betCount
  SELECT IFNULL(SUM(betAmounts),0) betAmounts,IFNULL(SUM(betCounts),0) betCounts,'BD_NUMBER' name FROM betCountTemp WHERE name REGEXP '^BD_[0-9]+$';

  INSERT INTO betCount
  SELECT IFNULL(betAmounts,0) betAmounts,IFNULL(betCounts,0) betCounts,name FROM betCountTemp WHERE name REGEXP '^FD_([a-z]|[A-Z])+$';

  INSERT INTO betCount
  SELECT IFNULL(betAmounts,0) betAmounts,IFNULL(betCounts,0) betCounts,name FROM betCountTemp WHERE name REGEXP '^LD_([a-z]|[A-Z])+$';

  INSERT INTO betCount
  SELECT IFNULL(betAmounts,0) betAmounts,IFNULL(betCounts,0) betCounts,name FROM betCountTemp WHERE name REGEXP '^TD_([a-z]|[A-Z])+$';

  INSERT INTO betCount
  SELECT IFNULL(betAmounts,0) betAmounts,IFNULL(betCounts,0) betCounts,name FROM betCountTemp WHERE name REGEXP '^BD_([a-z]|[A-Z])+$';

  SELECT betAmounts,betCounts,name FROM betCount;

END$$
DELIMITER;

/*
DROP PROCEDURE IF EXISTS `USP_BetCount`;
Call USP_BetCount(4,4312)
*/
