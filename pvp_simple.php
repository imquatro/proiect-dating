<!DOCTYPE html>
<html>
<head>
    <title>PVP Battles - Simple Version</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f0f0f0; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; }
        .match-card { 
            border: 2px solid #ddd; 
            margin: 10px; 
            padding: 15px; 
            border-radius: 10px; 
            background: #f9f9f9;
            display: inline-block;
            width: 300px;
            vertical-align: top;
        }
        .match-header { font-weight: bold; margin-bottom: 10px; }
        .match-players { display: flex; align-items: center; justify-content: space-between; }
        .player { text-align: center; }
        .player-avatar { width: 50px; height: 50px; border-radius: 50%; }
        .player-name { font-weight: bold; margin: 5px 0; }
        .player-score { color: #666; }
        .vs { font-size: 24px; font-weight: bold; color: #ff4444; margin: 0 20px; }
        .status { margin-top: 10px; padding: 5px; background: #e0e0e0; border-radius: 5px; text-align: center; }
        .loading { text-align: center; padding: 20px; }
        .error { color: red; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎮 PVP Battles - Simple Version</h1>
        <div id="loading" class="loading">Loading matches...</div>
        <div id="matches" style="display: none;"></div>
        <div id="error" class="error" style="display: none;"></div>
    </div>

    <script>
        async function loadMatches() {
            try {
                console.log('Loading matches...');
                
                const response = await fetch('pvp_api.php?action=getBracket&league_id=1');
                console.log('Response received:', response);
                
                const data = await response.json();
                console.log('Data received:', data);
                
                document.getElementById('loading').style.display = 'none';
                
                if (data.success && data.bracket && data.bracket.rounds) {
                    displayMatches(data.bracket.rounds);
                } else {
                    showError('No matches found: ' + JSON.stringify(data));
                }
                
            } catch (error) {
                console.error('Error loading matches:', error);
                document.getElementById('loading').style.display = 'none';
                showError('Error loading matches: ' + error.message);
            }
        }
        
        function displayMatches(matches) {
            const container = document.getElementById('matches');
            container.style.display = 'block';
            
            matches.forEach(match => {
                console.log('Creating match card for:', match);
                
                const matchCard = document.createElement('div');
                matchCard.className = 'match-card';
                
                const user1Photo = match.user1_photo || 'default-avatar.png';
                const user2Photo = match.user2_photo || 'default-avatar.png';
                
                matchCard.innerHTML = `
                    <div class="match-header">Match #${match.id}</div>
                    <div class="match-players">
                        <div class="player">
                            <img src="${user1Photo}" class="player-avatar" alt="${match.user1_name}">
                            <div class="player-name">${match.user1_name || '???'}</div>
                            <div class="player-score">Score: ${match.user1_score || 0}</div>
                        </div>
                        <div class="vs">VS</div>
                        <div class="player">
                            <img src="${user2Photo}" class="player-avatar" alt="${match.user2_name}">
                            <div class="player-name">${match.user2_name || '???'}</div>
                            <div class="player-score">Score: ${match.user2_score || 0}</div>
                        </div>
                    </div>
                    <div class="status">${match.completed ? 'Finished' : 'Active'}</div>
                `;
                
                container.appendChild(matchCard);
            });
        }
        
        function showError(message) {
            document.getElementById('error').style.display = 'block';
            document.getElementById('error').textContent = message;
        }
        
        // Load matches when page loads
        document.addEventListener('DOMContentLoaded', loadMatches);
    </script>
</body>
</html>
