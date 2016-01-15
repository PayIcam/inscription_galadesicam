SELECT * FROM 
    (SELECT ifnull(SUM( r.soirees ), 0) soireesW, ifnull(SUM( r.repas ), 0) repasW, ifnull(SUM( r.buffets ), 0) buffetsW
        FROM reservations_payicam AS r
    WHERE r.status = 'W') rW , 
    (SELECT ifnull(SUM( r.soirees ), 0) soireesV, ifnull(SUM( r.repas ), 0) repasV, ifnull(SUM( r.buffets ), 0) buffetsV
        FROM reservations_payicam AS r
    WHERE r.status = 'V') rV , 
    (SELECT COUNT( id ) soireesG , SUM( repas ) repasG , SUM( buffet ) buffetsG FROM guests) g 