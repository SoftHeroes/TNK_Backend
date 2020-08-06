DELIMITER $$
CREATE FUNCTION isFollowing(p_userToCheck bigint(20),p_userFollow bigint(20))
  RETURNS TinyInt
  DETERMINISTIC
BEGIN
  DECLARE op_isFollowing tinyint DEFAULT 0;

  IF EXISTS (SELECT 1 as isFollowing  FROM followUser WHERE followToID = p_userFollow AND followerID = p_userToCheck AND isFollowing = 'true') THEN
  BEGIN
    SET op_isFollowing = 1;
  END;
  END IF;

  return op_isFollowing;
END$$

DELIMITER ;
/*
DROP FUNCTION IF EXISTS isFollowing;
SELECT isFollowing(5,1);
*/
