<!DOCTYPE html>
<html>
<head>
    <title>Create Event</title>
    <style>
        .checklist-item {
            display: flex;
            margin-top: 5px;
        }
        .checklist-item input {
            flex: 1;
            margin-right: 5px;
        }
        .remove-button {
            background-color: red;
            color: white;
            border: none;
            padding: 4px;
            cursor: pointer;
        }
    </style>
    <script>
        function addChecklistItem() {
            const checklistContainer = document.getElementById('checklist-container');
            const checklistItem = document.createElement('div');
            checklistItem.className = 'checklist-item';

            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'checklist[]';
            input.placeholder = 'Checklist Item';
            input.required = true;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'remove-button';
            removeButton.textContent = 'Remove';
            removeButton.onclick = () => checklistContainer.removeChild(checklistItem);

            checklistItem.appendChild(input);
            checklistItem.appendChild(removeButton);
            checklistContainer.appendChild(checklistItem);
        }
    </script>
</head>
<body>
    <h2>Create Event</h2>
    <form method="post" action="../backend/process_create_event.php">
        <input type="text" name="title" placeholder="Event Title" required><br><br>
        <textarea name="description" placeholder="Event Description" required></textarea><br><br>
        <input type="date" name="date" required><br><br>
        <input type="time" name="time" required><br><br>
        
        <h3>Event Checklist</h3>
        <div id="checklist-container">
            <div class="checklist-item">
                <input type="text" name="checklist[]" placeholder="Checklist Item" required>
            </div>
        </div>
        <button type="button" onclick="addChecklistItem()">Add Another Checklist Item</button><br><br>

        <input type="submit" value="Create Event">
    </form>
</body>
</html>
