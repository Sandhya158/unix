<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: text/plain');

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

// Path to Git Bash executable (update the path if necessary)
$gitBashPath = "C:\\Program Files\\Git\\bin\\bash.exe";

// Check if a command is provided
if (isset($data['command'])) {
    $command = trim($data['command']); // Trim the command to remove extra spaces

    // Handle 'clear' command explicitly
    if ($command === "clear") {
        echo "CLEAR_SCREEN"; // Send a special response for the front end to handle
        exit;
    }

    // Sanitize the command to prevent injection
    $escapedCommand = escapeshellarg($command);
    $fullCommand = "\"$gitBashPath\" -c $escapedCommand"; // Construct the command

    // Execute the command using Git Bash
    $output = shell_exec($fullCommand);
    $exitCode = null;

    // To capture both output and exit code, use proc_open
    $descriptorSpec = [
        1 => ['pipe', 'w'], // Standard output
        2 => ['pipe', 'w'], // Standard error
    ];
    $process = proc_open($fullCommand, $descriptorSpec, $pipes);

    if (is_resource($process)) {
        $output = stream_get_contents($pipes[1]); // Capture standard output
        $errorOutput = stream_get_contents($pipes[2]); // Capture errors
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process); // Get the exit code
    }

    // If there's output, return it
    if ($output) {
        echo "$output";
    }

    // If there's an error, return the error message and exit code
    if ($exitCode !== 0) {
        echo "Error: $errorOutput";
        echo "Command failed with exit code $exitCode.";
    }

    // If no output or error is captured, show a generic message
    if (!$output && !$errorOutput) {
        echo "No output generated. Please check your command.";
    }
} else {
    // Display error message if no command is provided
    echo "Error: No command provided.";
}
?>
