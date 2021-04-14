update inspection
set inspector_id = null
where inspector_id not in (select id from employee);

update inspection set status = 1383
where (status = 1079 or status = 1078) 
and inspector_id is null;