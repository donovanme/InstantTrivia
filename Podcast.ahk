#SingleInstance Force

;	Array.prototype.slice.call(document.querySelectorAll('button'))
; .filter(function (el) {
;   return el.textContent === 'Confirm'
;  })[0];


; Podcast Ideas - filter out 'seen here'
; Smaller segments so whole thing doesn't conk out

getEpisodeFromDate(vdate)
{
	firstEpisodeDate := 20210210
	EnvSub, vdate, firstEpisodeDate, days
	return %vdate%
}

loadDetails(vdate)
{

  global episode_number
  global episode_file_name
  global episode_notes_file_name
  global episode_title
  global episode_notes
  global episode_date


; Get Episode Number
	episode_number := getEpisodeFromDate(vdate)
	GuiControl,,EpisodeNumber, Episode Number: %episode_number%

; Get Episode Name
	episode_file_name = % "c:\php\trivia\episodes\" . episode_number . "_episode_name.txt"	
	FileRead, episode_title, %episode_file_name%
	GuiControl,,EpisodeTitle, %episode_title%
	
	
; Get Episdoe Notes
	episode_notes_file_name = % "c:\php\trivia\episodes\" . episode_number . "_instant_trivia.html"	
	FileRead, episode_notes, %episode_notes_file_name%	

; Get Episode Date for calendar
	episode_date = % vdate
}

; Create GUI
	Gui,+AlwaysOnTop -SysMenu
	Gui, Add, MonthCal, vMyDateTime gDateRoutine
	Gui, Add, Text, w250 vEpisodeNumber , Episode Number: %episode_number%
	Gui, Add, Text, r4 w225 vEpisodeTitle, Episode Title: %episode_title% 
    ; Gui, Add, Text, , Episode Notes: %episode_notes%
	
	Gui, Add, Button, , Upload Page
	Gui, Add, Button, , Upload File
	Gui, Add, Button, , Fill Episode Title and Notes
	;Gui, Add, Button, , Generate Next Episode
	Gui, Add, Button, , Exit
	Gui, Show, NoActivate x1000 w250, Instant Podcast

loadDetails(CurrentDateTime) ; Load Episode Details for TODAY

return  ; End of auto-execute section. The script is idle until the user does something.




DateRoutine:

 Gui, Submit, NoHide
 loadDetails(MyDateTime)
 
 ;episodeNumber := getEpisodeFromDate(MyDateTime)
 ;statusText = %MyDateTime% Episode: %episodeNumber%
 ;GuiControl,,EpisodeTitle, %statusText%

Return


buttonExit:
	ExitApp
return

buttonGenerateNextEpisode:
	RunWait, C:\php\trivia\trivia.bat
	reload ; Reload the script so the current episode will be loaded
return


buttonUploadFile:
	WinActivate, ahk_exe chrome.exe  
			SendInput !d
			Sleep 100 
			SendInput javascript:document.querySelector(".css-24f90s").click();
			Sleep 100 
			SendInput {enter}
			Sleep 1100 ; Wait for File dialog to open
			SendInput c:\php\trivia\episodes\%episode_number%_instant_trivia.mp3{enter}
return

buttonUploadPage:
	WinActivate, ahk_exe chrome.exe  
			SendInput !d 
			Sleep 100
			SendInput https://anchor.fm/dashboard/episode/new{enter}
return

buttonFillEpisodeTitleandNotes:
	IfWinExist, ahk_exe chrome.exe 
	{	
		WinActivate 
			
			JS:="JavaScript:"
			JS.= "document.getElementById('title').focus();"
			
			Send !d
			Sleep 100
			SendInput %JS%{enter}
			clipboard := episode_title
			Sleep 200
			SendInput ^v {tab}{space}{tab}
			Sleep 500
			clipboard := episode_notes		
			SendInput ^v {tab 9}		
			SendInput %episode_number%
			SendInput {shift down}{tab 8}{shift up}{space}
			Sleep 1200 ; Wait for Calendar dialog to open
			;SendInput !d
			;Sleep 100 
			;SendInput javascript:document.querySelector("[data-value='2'].rdtDay").click();
			;SendInput {enter}
			;Sleep 200 
			;SendInput !d
			;Sleep 200
			;  Array.prototype.slice.call(document.querySelectorAll("button")).filter(function(el){return(el.textContent==="Confirm")})[0].click();
			;SendInput javascript:Array.prototype.slice.call(document.querySelectorAll(`%22button`%22)).filter(function(el)`%7Breturn(el.textContent`%3D`%3D`%3D`%22Confirm`%22)`%7D)`%5B0`%5D.click()`%3B
			;Sleep 200 
			;SendInput {enter}
			
		return
	}
return

^i::
	FormatTime, episode_date, episode_date, dd
	
	MsgBox %episode_date%
return


CoordMode, Mouse, Screen
^e::
	run "C:\Program Files\Notepad++\notepad++.exe" "%a_scriptfullpath%"
return

; Copy this function into your script to use it.
HideTrayTip() {
    TrayTip  ; Attempt to hide it the normal way.
    if SubStr(A_OSVersion,1,3) = "10." {
        Menu Tray, NoIcon
        Sleep 200  ; It may be necessary to adjust this sleep.
        Menu Tray, Icon
    }
}



;#IfWinActive ahk_class Notepad++
^w::
	SendInput ^s
	reload
	TrayTip Updated, saved
	Sleep 3000   ; Let it display for 3 seconds.
	HideTrayTip()
return
