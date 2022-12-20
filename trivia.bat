@echo off

SET loop_times=%1

IF [%1] == [] set loop_times=1

cd c:\php\trivia\
call c:\php\trivia\set_path.bat

for /L %%a in (1,1,%loop_times%) do (
	c:\php\php.exe c:\php\trivia\trivia.php
)

echo All done!

