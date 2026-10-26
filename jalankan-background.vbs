' ==============================================================
' BahariGIS - Silent Background Server Launcher (Tanpa Jendela CMD)
' ==============================================================
Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

' Dapatkan direktori skrip ini
scriptDir = fso.GetParentFolderName(WScript.ScriptFullName)
WshShell.CurrentDirectory = scriptDir

phpBin = "C:\xampp\php\php.exe"
If Not fso.FileExists(phpBin) Then
    phpBin = "php"
End If

' Cek apakah port 8000 sudah berjalan
Set oExec = WshShell.Exec("cmd /c netstat -ano | findstr /R /C "":8000 .*LISTENING""")
output = oExec.StdOut.ReadAll()

If InStr(output, "LISTENING") = 0 Then
    ' Jalankan PHP Server di background secara tersembunyi (0 = hidden)
    WshShell.Run "cmd /c """ & phpBin & """ -S localhost:8000", 0, False
    WScript.Sleep 1500
End If

' Buka browser
WshShell.Run "http://localhost:8000"
