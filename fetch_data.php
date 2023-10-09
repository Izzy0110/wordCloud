<?php
include 'dbConfig.php';

// var_dump($_POST);

if (isset($_POST['column']) && isset($_POST['table'])) { // Changed from $_POST to $_POST

    $selectedColumn = $_POST['column']; // Changed from $_POST to $_POST
    $table = $_POST['table']; // Changed from $_POST to $_POST

    $data = [];

    $commonWords = ["the", "be", "to", "of", "and", "a", "in", "that", "have", "it", "for", "not", "on", "with", "he", "as", "you", "do", "he", "as", "you", "do", "at", "this", "but", "his", "by", "from", "they", "we", "say", "her", "she", "or", "or", "an", "will", "my", "one", "all", "would", "there", "their", "what", "so", "up", "out", "if", "about", "who", "get", "which", "go", "me", "when", "make", "can", "like", "no", "its", "was", "i", "had", "could", "went", "how", "were", "way", "much", "is", "said", "think", "thought", "then", "down", "very", "again", "now", "quite", "im", "know", "off", "see", "into", "ill", "here", "such", "must", "did", "are", "got", "some", "your", "are", "them", "any", "has", "never", "only", "am", "more", "has", "been", "should", "other", "though", "dont", "didnt", "after", "came", "over", "come", "just", "back", "looked", "going", "before", "new", "us", "him", "something", "around", "turned", "want", "saw", "through", "made", "knew", "let", "shall", "himself", "take", "took", "well", "soon", "where", "last", "thing", "always", "little", "great", "good", "really", "too", "says", "feel", "than", "even", "enough", "under", "even", "long", "does", "why", "makes", "used", "behind", "things", "nothing", "above", "still", "upon", "every", "heard", "opening", "mans", "grew", "increase", "increased", "many", "whole", "put", "right", "each", "open", "large", "felt", "look", "A", "I", "actually", "Add", "DM", "All", "?", "App", "app"];

    $getDataSQL = "SELECT $selectedColumn FROM $table";
    $result = $conn->query($getDataSQL);

    while ($row = $result->fetch_assoc()) {
        // Split the feedback text into words
        $words = explode(" ", $row[$selectedColumn]);

        // Filter out common words
        $filteredWords = array_diff($words, $commonWords);

        // Add filtered words to the data array
        $data = array_merge($data, $filteredWords);
    }

    // Count word frequencies
    $wordCount = array_count_values($data);

    // Transform data into the required format for AnyChart (x: word, value: frequency)
    $finalData = [];
    foreach ($wordCount as $word => $count) {
        $finalData[] = ['x' => $word, 'value' => $count];
    }

    header('Content-Type: application/json');
    echo json_encode($finalData);
} else {
    echo "Column not selected";
}

$conn->close();
?>
