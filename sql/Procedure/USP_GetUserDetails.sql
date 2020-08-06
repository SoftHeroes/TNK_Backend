DELIMITER $$
CREATE PROCEDURE `USP_GetUserDetails` (IN p_PortalProviderID bigint UNSIGNED, IN p_UserID bigint UNSIGNED, IN p_StartDate date, IN p_EndDate date, IN p_AvatarPath varchar(255) , IN p_ProfilePath varchar(255))
  BEGIN

  DECLARE op_followerCount bigint;

  SET op_followerCount = (SELECT COUNT(1) FROM followUser WHERE followToID = p_UserID AND isFollowing = 'true' AND deletedAt IS NULL);

    DROP TEMPORARY TABLE IF EXISTS TempTopUsers;
    CREATE TEMPORARY TABLE TempTopUsers
    SELECT
      usr.PID userID,
      usr.UUID userUUID,
      usr.userName username,
      (CASE WHEN usr.profileImage IS NULL THEN CONCAT(p_AvatarPath,usr.avatar) ELSE CONCAT(p_ProfilePath,usr.profileImage) END) AS userImage,
      SUM(betAmount) totalBetAmount,
      SUM(bet.rollingAmount) totalWinAmount,
      COUNT(1) totalBets,
      COUNT(CASE WHEN bet.betResult = 1 THEN 1 ELSE NULL END) totalWinBets,
      ROUND(  ( COUNT(CASE WHEN bet.betResult = 1 THEN 1 ELSE NULL END) / COUNT(1) ) * 100 ,2) winRate,
      op_followerCount followerCount,
      usrSetting.isAllowToVisitProfile
    FROM betting bet
      JOIN user usr ON usr.PID = bet.userID
      JOIN userSetting usrSetting ON usr.PID = usrSetting.userID
      JOIN game gm ON gm.PID = bet.gameID
      JOIN providerGameSetup pgs ON pgs.PID = gm.providerGameSetupID AND pgs.portalProviderID = p_PortalProviderID
    WHERE bet.createdDate <= p_EndDate AND bet.createdDate >= p_StartDate
    GROUP BY bet.userID ORDER BY winRate ,SUM(bet.rollingAmount)  DESC ;

    CREATE TEMPORARY TABLE TempUser
    SELECT userID FROM TempTopUsers;

    INSERT INTO TempTopUsers(userID,userUUID,username,userImage,totalBetAmount,totalWinAmount,totalBets,totalWinBets,winRate,followerCount,isAllowToVisitProfile)
    SELECT
      usr.PID,
      usr.UUID,
      usr.userName,
      (CASE WHEN usr.profileImage IS NULL THEN CONCAT(p_AvatarPath,usr.avatar) ELSE CONCAT(p_ProfilePath,usr.profileImage) END) AS profileImage,
      0,
      0,
      0,
      0,
      0,
      op_followerCount,
      usrSetting.isAllowToVisitProfile
    FROM user usr
    JOIN userSetting usrSetting ON usr.PID = usrSetting.userID
    WHERE portalProviderID = p_PortalProviderID AND usr.PID NOT IN (SELECT userID FROM TempUser);

    set @rank := 0;

    DROP TEMPORARY TABLE IF EXISTS TopUsers;
    CREATE TEMPORARY TABLE TopUsers
    SELECT userID,userUUID,username,userImage,ROUND(totalBetAmount,2) totalBetAmount,ROUND(totalWinAmount,2) totalWinAmount,totalBets,totalWinBets,winRate,followerCount,isAllowToVisitProfile,@rank := @rank + 1 Rank FROM TempTopUsers
    ORDER BY winRate DESC,totalBetAmount DESC,totalBets DESC ;

    SELECT userUUID,username,userImage,totalBetAmount,totalWinAmount,totalBets,totalWinBets,winRate,followerCount,isAllowToVisitProfile,Rank as rank FROM TopUsers WHERE userID = p_UserID;

  END$$
DELIMITER;

/*
DROP PROCEDURE IF EXISTS `USP_GetUserDetails`;
Call USP_GetUserDetails(4,20,'2020-04-01','2020-04-07','images/user/avatar/','images/user/profile/');
*/

