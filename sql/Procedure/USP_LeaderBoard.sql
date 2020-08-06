DELIMITER $$
CREATE PROCEDURE `USP_LeaderBoard` (IN p_PortalProviderID bigint UNSIGNED, IN p_UserID bigint UNSIGNED, IN p_StartDate date, IN p_EndDate date , IN p_Limit INT, IN p_AvatarPath varchar(255) , IN p_ProfilePath varchar(255))
  BEGIN

    DROP TEMPORARY TABLE IF EXISTS TopUsers;
    CREATE TEMPORARY TABLE TopUsers
    SELECT
      usr.UUID userUUID,
      usr.userName username,
      (CASE WHEN usr.profileImage IS NULL THEN CONCAT(p_AvatarPath,usr.avatar) ELSE CONCAT(p_ProfilePath,usr.profileImage) END) AS userImage,
      SUM(betAmount) totalBetAmount,
      SUM(bet.rollingAmount) totalWinAmount,
      COUNT(1) totalBets,
      COUNT(CASE WHEN bet.betResult = 1 THEN 1 ELSE NULL END) totalWinBets,
      ROUND(  ( COUNT(CASE WHEN bet.betResult = 1 THEN 1 ELSE NULL END) / COUNT(1) ) * 100 ,2) winRate,
      CASE WHEN p_UserID = usr.PID THEN -1 ELSE CASE WHEN folUsr.PID IS NULL THEN 0 ELSE 1 END END isFollowing,
      usr.country country,
      usrSet.isAllowToLocation isAllowToLocation
    FROM betting bet
      JOIN user usr ON usr.PID = bet.userID
      JOIN userSetting usrSet ON usr.PID = usrSet.userID
      JOIN game gm ON gm.PID = bet.gameID
      JOIN providerGameSetup pgs ON pgs.PID = gm.providerGameSetupID AND pgs.portalProviderID = p_PortalProviderID
      LEFT JOIN followUser folUsr ON usr.PID = folUsr.followToID AND p_UserID = folUsr.followerID AND folUsr.isFollowing = 'true'
    WHERE bet.createdDate <= p_EndDate AND bet.createdDate >= p_StartDate AND usr.portalProviderID = p_PortalProviderID
    GROUP BY bet.userID ORDER BY winRate DESC,SUM(bet.rollingAmount)  DESC,totalBets DESC LIMIT p_Limit;

    set @rank := 0;
    SELECT userUUID,username,userImage,ROUND(totalBetAmount,2) totalBetAmount,ROUND(totalWinAmount,2) totalWinAmount,totalBets,totalWinBets,winRate,isFollowing,country,isAllowToLocation,@rank := @rank + 1 as rank FROM TopUsers
    ORDER BY winRate DESC,totalBetAmount DESC,totalBets DESC ;

  END$$
DELIMITER;

/*
DROP PROCEDURE IF EXISTS `USP_LeaderBoard`;
Call USP_LeaderBoard(4,10,'1970-01-31','2020-05-20',20,'images/user/avatar/','images/user/profile/')
*/
