package main

import (
	"bufio"
	"fmt"
	"os"
	"os/exec"
	"strconv"
	"strings"
	"time"
)

const BASH_SCRIPT = "cd '#F' && php -S 127.0.0.1:#P"
const PMA string = "/mnt/DATA/Programming/Web/phpMyAdmin-5.2.2-all-languages"
const PMA_PORT int = 5500
const DEFAULT_PROJ_PORT = 5501
const PROJ_PREFIX = "PROJ"

type serverRunner struct {
	path          string
	port          int
	prefix        string
	outputScanner *bufio.Scanner
	errorScanner  *bufio.Scanner
	command       *exec.Cmd
	isRunning     bool
}

func newServerRunner(path string, port int, prefix string) *serverRunner {
	return &serverRunner{path: path, port: port, prefix: prefix}
}

func (r *serverRunner) execServer() error {
	r.isRunning = true
	r.command = exec.Command("bash", "-c", strings.Replace(strings.Replace(BASH_SCRIPT, "#F", r.path, 1), "#P", strconv.Itoa(r.port), 1))
	//Get STD:OUT
	oPipe, err1 := r.command.StdoutPipe()
	if err1 != nil {
		r.isRunning = false
		return err1
	}
	r.outputScanner = bufio.NewScanner(oPipe)

	//Get STD:ERR
	ePipe, err2 := r.command.StderrPipe()
	if err2 != nil {
		r.isRunning = false
		return err2
	}
	r.errorScanner = bufio.NewScanner(ePipe)

	//Run command
	err3 := r.command.Start()
	if err3 != nil {
		return err3
	}

	go r.printOutputs()
	go r.printErrors()

	err4 := r.command.Wait()
	r.isRunning = false

	return err4
}

func (r *serverRunner) printOutputs() {
	for r.outputScanner.Scan() {
		fmt.Println(r.prefix + ": " + r.outputScanner.Text())
	}
}
func (r *serverRunner) printErrors() {
	for r.errorScanner.Scan() {
		fmt.Println(r.prefix + ": " + r.errorScanner.Text())
	}
}

func (r *serverRunner) startWebBrowser() {
	fmt.Println(r.prefix + " - Starting web browser...")
	time.Sleep(1000 * time.Microsecond)
	exec.Command("xdg-open", "127.0.0.1:"+strconv.Itoa(r.port)).Start()
	fmt.Println(r.prefix + " - Opened web browser!")
}

var waitingForSudo = true
var runners []*serverRunner = make([]*serverRunner, 0)

func startMariaDB() error {
	fmt.Println("Starting MariaDB...")
	waitingForSudo = true

	//Check if service is running
	cmd := exec.Command("systemctl", "is-active", "mariadb")
	isActiveCheck, _ := cmd.CombinedOutput()

	if !strings.HasPrefix(string(isActiveCheck), "active") {
		//Start service
		err2 := exec.Command("sudo", "systemctl", "start", "mariadb").Run()
		fmt.Println("Stared MariaDB!")
		waitingForSudo = false
		return err2
	} else {
		//Service already running
		fmt.Println("MariaDB already running!")
		waitingForSudo = false
	}
	return nil
}

func startPHPServer(r *serverRunner) error {
	fmt.Println(r.prefix + " - Starting PHP server...")

	//Start mariadb + Request sudo
	err1 := startMariaDB()
	if err1 != nil {
		fmt.Println(r.prefix + " - Error staring MariaDB: " + err1.Error())
		return err1
	}

	//Start PHP server
	err2 := r.execServer()
	if err2 != nil {
		fmt.Println(r.prefix + " - Error staring PHP server with prefix " + r.prefix + ": " + err2.Error())
	}

	fmt.Println(r.prefix + " - Stopped PHP server!")
	return err2
}

func startPHPProject(path string, port int, prefix string) {
	//Create runner
	r := newServerRunner(path, port, prefix)
	runners = append(runners, r)

	//Start server in goroutine
	go startPHPServer(r)

	//Wait for sudo
	for waitingForSudo {
		time.Sleep(1 * time.Second)
	}

	//Safe sleep
	time.Sleep(1 * time.Second)
	r.startWebBrowser()
}

func ReadLine() (string, error) {
	reader := bufio.NewReader(os.Stdin)
	read, err := reader.ReadString('\n')
	if err != nil {
		return "", err
	}
	return strings.ReplaceAll(read, "\n", ""), nil
}

func startPMA() {
	//PHPMyAdmin
	startPHPProject(PMA, 5500, "PMA")
}

func main() {
	fmt.Println("Vítejte ve spouštěči PHP + PHP My Admin")
	fmt.Println("PHP My Admin se spustí společně s níže uvedený projektem.")
	fmt.Println("Před přihlášením si prosím nastavte root heslo pro MariaDB, které budete poté používat v PHP My Adminovi a ve Vašich aplikacích: https://wiki.archlinux.org/title/MariaDB#Reset_the_root_password")
	fmt.Println("PHP My Admin při spuštění někdy vrátí error -> Stačí znovu načíst stránku v prohlížeči a vše bude v pořádku.")
	fmt.Println("Stisknutím CTRL+C se ukončí veškeré PHP servery běžící pod tímto programem (MariaDB zůstane v provozu).")

	//Get path
	fmt.Print("Vložte cestu k projektu: ")
	path, err := ReadLine()
	if err != nil {
		fmt.Println(err.Error())
		return
	}

	//Get port
	fmt.Print("Vložte port projektu [" + strconv.Itoa(DEFAULT_PROJ_PORT) + "]: ")
	portStr, err2 := ReadLine()
	if err2 != nil {
		fmt.Println(err2.Error())
		return
	}
	var port = DEFAULT_PROJ_PORT
	if portStr != "" {
		var err3 error
		port, err3 = strconv.Atoi(portStr)
		if err3 != nil {
			fmt.Println(err3.Error())
			port = DEFAULT_PROJ_PORT
		}
	}

	//Start PHP
	startPMA()
	if path != "" {
		startPHPProject(path, port, PROJ_PREFIX)
	}

	//Wait for all exit
	holdExit()
}

func holdExit() {
	//Check if server is running
	var isRunning bool = false
	for i := 0; i < len(runners); i++ {
		if runners[i].isRunning {
			isRunning = true
			break
		}
	}

	if !isRunning {
		return
	}

	//Print all outputs
	for i := 0; i < len(runners); i++ {
		runners[i].printOutputs()
	}

	//Sleep and run again
	time.Sleep(10 * time.Millisecond)
	holdExit()
}
