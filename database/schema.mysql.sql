DROP TABLE IF EXISTS Flags; 
DROP TABLE IF EXISTS Searches; 

CREATE TABLE Flags ( 
    ownerId         BIGINT UNSIGNED NOT NULL, 
    targetId        BIGINT UNSIGNED NOT NULL, 
    targetType      SMALLINT UNSIGNED NOT NULL,
    flagType        SMALLINT UNSIGNED NOT NULL,
    flagValue       char(64) NOT NULL,
    flagDate        timestamp,

    INDEX (ownerId),   
    INDEX (targetId),  
    INDEX (targetType),
    INDEX (flagType),
    INDEX (flagValue),
    
    PRIMARY KEY(ownerId,targetId,targetType)             
) CHARSET=UTF8 COLLATE utf8_general_ci;

CREATE TABLE Searches (
    searchId        INT UNSIGNED NOT NULL AUTO_INCREMENT, 
    ownerId         BIGINT UNSIGNED NOT NULL,
    saveName        char(64) NOT NULL,    
    queryString     char(140) NOT NULL,
    
    index(ownerId),
    primary key (searchId)
) CHARSET=UTF8 COLLATE utf8_general_ci; 

/*

Some fake searches
INSERT INTO Searches (searchId,ownerId,saveName,queryString) VALUES 
    (null,25358977,'from me','from:mostlygeek'),
    (null,25358977,'<html>inject</html>','matty'),
    (null,25358977,'mentions me','@mostlygeek');


TRUNCATE Flags; 

REPLACE INTO Flags (ownerId,targetid,targetType,flagType,flagValue,flagDate)
    SELECT uid,statusid,1,1,'1',readon FROM StatusReads;

REPLACE INTO Flags (ownerId,targetid,targetType,flagType,flagValue)
    SELECT uid,statusId,1,2,showStatus FROM StatusShows;

REPLACE INTO Flags (ownerId,targetid,targetType,flagType,flagValue,flagDate)
    SELECT uid,messageId,2,1,'1',readon FROM MessageReads;

REPLACE INTO Flags (ownerId,targetid,targetType,flagType,flagValue)
    SELECT uid,messageId,2,2,showStatus FROM MessageShows;


These are no longer needed.
DROP TABLE IF EXISTS StatusReads; 
DROP TABLE IF EXISTS StatusShows; 
DROP TABLE IF EXISTS MessageReads; 
DROP TABLE IF EXISTS MessageShows; 

CREATE TABLE StatusReads (
    uid         BIGINT UNSIGNED NOT NULL, 
    statusid    BIGINT UNSIGNED NOT NULL, 
    readon      timestamp NOT NULL,
    
    index (uid), 
    index (statusid),
    primary key (uid,statusid)
    
) CHARSET=UTF8 COLLATE utf8_general_ci;

CREATE TABLE StatusShows (
    uid         BIGINT UNSIGNED NOT NULL, 
    statusId    BIGINT UNSIGNED NOT NULL, 
    showStatus  TINYINT NOT NULL DEFAULT 1, #0 = hide, 1 = show
    
    index (uid), 
    index (statusid),
    primary key (uid,statusid)
    
) CHARSET=UTF8 COLLATE utf8_general_ci;


CREATE TABLE MessageReads (
    uid         BIGINT UNSIGNED NOT NULL, 
    messageId   BIGINT UNSIGNED NOT NULL, 
    readon      timestamp NOT NULL,
    
    index (uid), 
    index (messageId),
    primary key (uid,messageId)
    
) CHARSET=UTF8 COLLATE utf8_general_ci;

CREATE TABLE MessageShows (
    uid         BIGINT UNSIGNED NOT NULL, 
    messageId   BIGINT UNSIGNED NOT NULL, 
    showStatus  TINYINT NOT NULL DEFAULT 1, #0 = hide, 1 = show
    
    index (uid), 
    index (messageId),
    primary key (uid,messageId)
    
) CHARSET=UTF8 COLLATE utf8_general_ci;


*/