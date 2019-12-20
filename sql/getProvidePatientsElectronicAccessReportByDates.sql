DROP PROCEDURE IF EXISTS `getProvidePatientsElectronicAccessReportByDates`;

DELIMITER $$
CREATE PROCEDURE `getProvidePatientsElectronicAccessReportByDates` (IN provider_id INT, IN start_date DATE, IN end_date DATE, IN stages VARCHAR(40))
BEGIN
    DROP TABLE IF EXISTS g2_report_ds;
    DROP TABLE IF EXISTS g2_report_ds_first_encounters;
    DROP TABLE IF EXISTS g2_report_denominator_ds;
    DROP TABLE IF EXISTS g2_report_numerator_ds;

    SET @provider = (SELECT CONCAT(title, ' ', lname, ', ', fname, ' ', mname, ' (NPI:', npi, ')') FROM users WHERE id = provider_id);

    CREATE TEMPORARY TABLE g2_report_ds
    SELECT
        r.eid,
        r.pid,
        r.provider,
        r.facility,
        r.in_time,
        r.service_date,
        r.event_id,
        r.event_date
    FROM
        (SELECT
             e.eid,
             e.provider_uid as provider,
             e.facility,
             e.pid,
             e.service_date,
             a.id AS event_id,
             a.event_date,
             IF(e.service_date IS NOT NULL
                    AND CAST(CONCAT(DATE(a.event_date), ' 23:59:59') AS DATETIME) < DATE_ADD(e.service_date, INTERVAL 48 HOUR), 1, 0) AS in_time
         FROM
             encounters AS e
                 LEFT JOIN audit_log AS a ON  e.eid = a.eid AND a.event IN ('CCDA_RECEIVED')
         WHERE
                 e.provider_uid = provider_id
           AND e.service_date IS NOT NULL
           AND e.service_date BETWEEN start_date AND end_date

        ) r ORDER BY r.service_date;


    CREATE TEMPORARY TABLE g2_report_ds_first_encounters SELECT eid, pid,provider,facility FROM g2_report_ds GROUP BY pid, provider;

    SET @stage3 = (SELECT FIND_IN_SET('3',stages));

    IF @stage3 > 0 THEN

        CREATE TEMPORARY TABLE g2_report_denominator_ds
        SELECT 1 as `value`, pid FROM g2_report_ds GROUP BY pid;

        CREATE TEMPORARY TABLE g2_report_numerator_ds
        SELECT 1 as `value`, pid FROM g2_report_ds WHERE in_time = '1' AND eid IN (SELECT eid FROM g2_report_ds_first_encounters) group by pid;

        DELETE FROM  g2_report_numerator_ds WHERE pid IN (
            SELECT pid FROM g2_report_ds WHERE in_time = '0' AND eid NOT IN (SELECT eid FROM g2_report_ds_first_encounters) group by pid
        );

        SET @denominator = (SELECT sum(`value`) FROM g2_report_denominator_ds);
        SET @numerator = (SELECT sum(`value`) FROM g2_report_numerator_ds);

        SELECT
            @provider as provider,
            'Stage 3 Measure' as title,
            @denominator as denominator,
            (SELECT group_concat(pid) FROM (SELECT pid FROM g2_report_denominator_ds GROUP BY pid) as d) as denominator_pids,
            @numerator as numerator,
            (SELECT group_concat(pid) FROM (SELECT pid FROM g2_report_numerator_ds GROUP BY pid) as n) as numerator_pids;

    END IF ;




END$$

DELIMITER ;

