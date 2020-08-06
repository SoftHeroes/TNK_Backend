DELIMITER $$
CREATE PROCEDURE `USP_ActiveGames` (IN p_PortalProviderID bigint UNSIGNED, IN p_Limit int, IN p_Offset int, IN p_StockID int)
BEGIN

  DROP TEMPORARY TABLE IF EXISTS getGamesTemp;
  CREATE TEMPORARY TABLE getGamesTemp
  SELECT
    game.UUID AS gameID,
    MAX(game.startTime) AS gameStartTime,
    stock.stockLoop AS stockLoop,
    stock.name AS stockName,
    stock.category AS stockType,
    game.gameStatus AS gameStatusCode,
    (CASE WHEN game.gameStatus = 0 THEN "Pending" WHEN game.gameStatus = 1 THEN "Open" WHEN game.gameStatus = 2 THEN "Close" WHEN game.gameStatus = 3 THEN "Complete" WHEN game.gameStatus = 4 THEN "Error" ELSE "Deleted" END) AS gameStatus
  FROM `game`
    INNER JOIN `stock` ON `game`.`stockID` = `stock`.`PID`
    INNER JOIN `providerGameSetup` ON `game`.`providerGameSetupID` = `providerGameSetup`.`PID`
    INNER JOIN `portalProvider` ON `providerGameSetup`.`portalProviderID` = `portalProvider`.`PID`
  WHERE `portalProvider`.`isActive` = 'active'
  AND `portalProvider`.`deletedAt` IS NULL
  AND `providerGameSetup`.`portalProviderID` = p_PortalProviderID
  AND `game`.`gameStatus` IN (1, 2)
  GROUP BY stockName,stockLoop,gameStatus
  LIMIT p_Limit OFFSET p_Offset;


  SELECT gameID,gameStartTime,stockLoop,stockName,stockType,gameStatusCode,gameStatus FROM getGamesTemp
  GROUP BY stockName
  ORDER BY gameStatusCode;

END$$
DELIMITER;

/*
DROP PROCEDURE IF EXISTS `USP_ActiveGames`;
Call USP_ActiveGames(4,100,0,null)
*/
