delimiter //
create PROCEDURE init()

BEGIN 

DECLARE ii int;
declare vname varchar(32);
DECLARE vpassword varchar(32);

set vname = 'name';
set vpassword = 'password';
set ii = 1;

while( ii < 1000 ) DO
	insert into test123 ( name , password , age ) values( vname , vpassword , ii );
	set ii = ii + 1;
end WHILE;

end
//

call init;