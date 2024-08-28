<?php 

$dt=json_decode((shell_exec("ubus call system board")), true); 
// MACHINE INFO 
$devices=$dt['model']; 

// OS TYPE AND KERNEL VERSION 
$kernelv=exec( "cat /proc/sys/kernel/ostype").' '.exec("cat /proc/sys/kernel/osrelease"); 
$OSVer=$dt['release']['distribution']." ". $dt['release']['version']; 

// MEMORY INFO 
$tmpramTotal=exec("cat /proc/meminfo | grep MemTotal | awk '{print $2}'"); 
$tmpramAvailable=exec("cat / proc/meminfo | grep MemAvailable | awk '{print $2}'"); 

$ramTotal=number_format(($tmpramTotal/1000),1); 
$ramAvailable=number_format(($tmpramAvailable/1000),1); 
$ramUsage=number_format((($tmpramTotal-$tmpramAvailable)/1000),1); 

// UPTIME 
$raw_uptime = exec("cat /proc/uptime | awk '{print $1}'"); 
$days = floor($raw_uptime / 86400); 
$hours = floor(($raw_uptime / 3600) % 24) ; 
$minutes = floor(($raw_uptime / 60) % 60); 
$seconds = $raw_uptime % 60; 


// CPU FREQUENCY 
/* $cpuFreq = file_get_contents("/sys/devices/system/cpu/cpu0/cpufreq/scaling_cur_freq"); 
$cpuFreq = round($cpuFreq / 1000, 1); 

// CPU TEMPERATURE 
$cpuTemp = file_get_contents("/sys/class/thermal/ thermal_zone0/temp"); 
$cpuTemp = round($cpuTemp / 1000, 1); 
if ($cpuTemp >= 60) { 
    $color = "red"; 
} elseif ($cpuTemp >= 50) { 
    $color = "orange "; 
} else { 
    $color = "white"; 
} 

*/ 

// CPU LOAD AVERAGE 
$cpuLoad = shell_exec("cat /proc/loadavg"); 
$cpuLoad = explode(' ', $cpuLoad); 
$cpuLoadAvg1Min = round($cpuLoad[0], 2); 
$cpuLoadAvg5Min = round($cpuLoad[1], 2); 
$cpuLoadAvg15Min = round($cpuLoad[2], 2); 

// CPU INFORMATION 
/* $cpuInfo = shell_exec( "lscpu"); 
$cpuCores = preg_match('/^CPU\(s\):\s+(\d+)/m', $cpuInfo, $matches); 
$cpuThreads = preg_match('/^Thread\(s\ ) per core:\s+(\d+)/m', $cpuInfo, $matches); 
$cpuModelName = preg_match('/^Model name:\s+(.+)/m', $cpuInfo, $matches); 
$ cpuFamily = preg_match('/^CPU family:\s+(.+)/m', $cpuInfo, $matches); 
*/ 
?> 

<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>GitHub Music Player</title> 
    <style> 
        body { 
            font-family: Arial, sans-serif; 
            overflow: hidden; 
        } 

        #container { 
            text-align: center;
            margin-top: 50px; 
        } 

        #player { 
            width: 320px; 
            height: 320px; 
            margin: 50px auto; 
            padding: 20px; 
            background: url('/nekoclash/assets/img/3.svg') no-repeat center center; 
            background-size: cover; 
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            border-radius: 50%; 
            transform-style : preserve-3d; 
            transition: transform 0.5s; 
            position: relative; 
            animation: rainbow 5s infinite, rotatePlayer 10s linear infinite; 
        } 

        #player:hover { 
            transform: rotateY(360deg) rotateX(360deg); 
        } 

        #player h2 { 
            margin- top: 0; 
        } 

        #controls { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
        } 

        button { 
            background-color: #4CAF50; 
            border: none; 
            color: white; 
            padding: 10px 20px; 
            text-align: center; 
            text-decoration: none; 
            display: inline-block ; 
            font-size: 16px; 
            margin: 4px 2px; 
            cursor: pointer; 
            box-shadow: 0 4px #666; 
            transition: transform 0.2s, box-shadow 0.2s; 
        } 

        button:active { 
            transform: translateY(4px); 
            box -shadow: 0 2px #444; 
        } 

        @keyframes rotatePlayer { 
            0% { transform: rotate(0deg); } 
            100% { transform: rotate(360deg); } 
        } 

        #hidePlayer, #timeDisplay { 
            font-size: 24px; 
            font- weight: bold; 
            margin: 10px 0; 
            background: linear-gradient(90deg, #FF0000, #FF7F00, #FFFF00, #00FF00, #0000FF, #4B0082, #9400D3); 
            -webkit-background-clip: text; 
            color: transparent; 
            transition: background 1s ease; 
        } 

        .rounded-button { 
            border-radius : 30px 15px; 
        } 

        #tooltip { 
            position: absolute;
            background-color: green; 
            color: #fff; 
            padding: 5px; 
            border-radius: 5px; 
            display: none; 
        } 

        #mobile-controls { 
            margin-top: 20px; 
            transition: opacity 1s ease-in-out; 
            opacity: 1 ; 
        } 

        #mobile-controls.hidden { 
            opacity: 0; 
            pointer-events: none; 
        } 

        @media (min-width: 768px) { 
            #mobile-controls { 
                display: none; 
            } 
        } 

        @media (max-width: 767px) { 
            #mobile-controls { 
                display: block; 
            } 
        } 
    </style> 
</head> 
<body> 
    <div id="player" onclick="toggleAnimation()"> 
        <p id="hidePlayer">Mihomo</p > 
        <p id="timeDisplay">00:00</p> 
        <audio id="audioPlayer" controls> 
            <source src="" type="audio/mpeg"> 
            Your browser does not support audio playback. 
        </audio> 
        <br> 
        <div id="controls"> 
            <button id="prev" class="rounded-button">⏮️</button> 
            <button id="orderLoop" class="rounded-button"> 🔁</button> 
            <button id="play" class="rounded-button">⏸️</button> 
            <button id="next" class="rounded-button">⏭️</button> 
        </div> 
    < /div> 
    <div id="mobile-controls"> 
        <button id="togglePlay" class="rounded-button">Play/Pause</button> 
        <button id="toggleEnable" class="rounded-button"> Enable/disable</button> 
    </div> 
    <div id="tooltip"></div> 

    <script> 
        let colors = ['#FF0000', '#FF7F00', '#FFFF00', '#00FF00', '#0000FF', '#4B0082', '#9400D3']; 
        let isPlayingAllowed = false; 
        let isLooping = false; 
        let isOrdered = false; 
        let currentSongIndex = 0; 
        let songs = []; 
        const audioPlayer = document.getElementById('audioPlayer'); 

        function toggleAnimation() { 
            const player = document.getElementById('player'); 
            if (player.style.animationPlayState === 'paused') { 
                player.style.animationPlayState = 'running'; 
            } else { 
                player.style.animationPlayState = 'paused'; 
            } 
        }

        var hidePlayerButton = document.getElementById('hidePlayer'); 
        hidePlayerButton.addEventListener('click', function() { 
            var player = document.getElementById('player'); 
            if (player.style.display === 'none') { 
                player.style.display = 'flex'; 
            } else { 
                player.style.display = 'none'; 
            } 
        }); 

        function applyGradient(text, elementId) { 
            const element = document.getElementById(elementId); 
            element.innerHTML = ''; 
            for (let i = 0; i < text.length; i++) { 
                const span = document.createElement('span'); 
                span.textContent = text[i]; 
                span.style.color = colors[i % colors.length]; 
                element.appendChild(span); 
            } 
            const firstColor = colors.shift(); 
            colors.push(firstColor); 
        } 

        function updateTime() { 
            const now = new Date(); 
            const hours = now.getHours(); 
            const timeString = now.toLocaleTimeString('zh-CN', { hour12:false }); 
            let ancientTime; 

            if (hours >= 23 || hours < 1) { 
                ancientTime = '子时'; 
            } else if (hours >= 1 && hours < 3) { 
                ancientTime = 'Chou Shi'; 
            } else if (hours >= 3 && hours < 5) { 
                ancientTime = '寅时'; 
            } else if (hours >= 5 && hours < 7) { 
                ancientTime = '卯时'; 
            } else if (hours >= 7 && hours < 9) { 
                ancientTime = '陈时'; 
            } else if (hours >= 9 && hours < 11) { 
                ancientTime = 'Sishi'; 
            } else if (hours >= 11 && hours < 13) { 
                ancientTime = 'noon hour'; 
            } else if (hours >= 13 && hours < 15) { 
                ancientTime = '不时'; 
            } else if (hours >= 15 && hours < 17) { 
                ancientTime = 'Shenshi'; 
            } else if (hours >= 17 && hours < 19) { 
                ancientTime = 'Youshi'; 
            } else if (hours >= 19 && hours < 21) { 
                ancientTime = 'Xu Shi'; 
            } else { 
                ancientTime = 'Hai Shi'; 
            } 

            const displayString = `${timeString} (${ancientTime})`; 
            applyGradient(displayString, 'timeDisplay'); 
        }

        applyGradient('Mihomo', 'hidePlayer'); 
        updateTime(); 
        setInterval(updateTime, 1000); 

        function showTooltip(text) { 
            const tooltip = document.getElementById('tooltip'); 
            tooltip.textContent = text; 
            tooltip.style. display = 'block'; 
            tooltip.style.left = (window.innerWidth - tooltip.offsetWidth - 20) + 'px'; 
            tooltip.style.top = '10px'; 
            setTimeout(hideTooltip, 5000); 
        } 

        function hideTooltip() { 
            const tooltip = document.getElementById('tooltip'); 
            tooltip.style.display = 'none'; 
        } 

        function handlePlayPause() { 
            const playButton = document.getElementById('play'); 
            if (isPlayingAllowed) { 
                if (audioPlayer.paused) { 
                    showTooltip('play'); 
                    audioPlayer.play(); 
                    playButton.textContent = 'Pause'; 
                } else { 
                    showTooltip('Pause play'); 
                    audioPlayer.pause(); 
                    playButton.textContent = 'Play '; 
                } 
            } else { 
                showTooltip('Play is prohibited'); 
                audioPlayer.pause(); 
            } 
        } 

        function handleOrderLoop() { 
            if (isPlayingAllowed) { 
                const orderLoopButton = document.getElementById('orderLoop'); 
                if (isOrdered) { 
                    isOrdered = false; 
                    isLooping = !isLooping; 
                    orderLoopButton.textContent = isLooping ? '循环' : ''; 
                    showTooltip(isLooping ? '环播放' : '停止循环'); 
                } else { 
                    isOrdered = true; 
                    isLooping = false; 
                    orderLoopButton.textContent = 'Shun'; 
                    showTooltip('Order Play'); 
                } 
            } 
        } 

        document.addEventListener('keydown', function(event) { 
            switch (event.key) { 
                case 'ArrowLeft': 
                    document.getElementById('prev ').click(); 
                    break; 
                case 'ArrowRight':
                    document.getElementById('next').click(); 
                    break; 
                case ' ':
                    handlePlayPause(); 
                    break; 
                case 'ArrowUp': 
                    handleOrderLoop(); 
                    break; 
                case 'Escape': 
                    isPlayingAllowed = !isPlayingAllowed; 
                    if (!isPlayingAllowed) { 
                        audioPlayer.pause(); 
                        audioPlayer.src = ''; 
                        showTooltip('Play Disabled'); 
                    } else { 
                        showTooltip('Play is enabled'); 
                        if (songs.length > 0) { 
                            loadSong(currentSongIndex); 
                        } 
                    } 
                    break; 
            } 
        }); 

        document.getElementById('play').addEventListener( 'click', handlePlayPause); 
        document.getElementById('next').addEventListener('click', function() { 
            if (isPlayingAllowed) { 
                currentSongIndex = (currentSongIndex + 1) % songs.length; 
                loadSong(currentSongIndex); 
                showTooltip( 'Next song'); 
            } else { 
                showTooltip('Playing is prohibited'); 
            } 
        }); 
        document.getElementById('prev').addEventListener('click', function() { 
            if (isPlayingAllowed) { 
                currentSongIndex = (currentSongIndex - 1 + songs.length ) % songs.length; 
                loadSong(currentSongIndex); 
                showTooltip('previous song'); 
            } else { 
                showTooltip('Playback is prohibited'); 
            } 
        }); 
        document.getElementById('orderLoop').addEventListener('click' , handleOrderLoop); 

        document.getElementById('togglePlay').addEventListener('click', handlePlayPause); 
        document.getElementById('toggleEnable').addEventListener('click', function() { 
            isPlayingAllowed = !isPlayingAllowed; 
            if (!isPlayingAllowed ) { 
                audioPlayer.pause(); 
                audioPlayer.src = ''; 
                showTooltip('Playback disabled'); 
            } else { 
                showTooltip('Playback enabled'); 
                if (songs.length > 0) { 
                    loadSong(currentSongIndex); 
                } 
            } 
        }); 

        function loadSong(index) {
            if (isPlayingAllowed && index >= 0 && index < songs.length) { 
                audioPlayer.src = songs[index]; 
                audioPlayer.play(); 
            } else { 
                audioPlayer.pause(); 
            } 
        } 

        audioPlayer.addEventListener('ended', function() { 
            if (isPlayingAllowed) { 
                if (isLooping) { 
                    audioPlayer.currentTime = 0; 
                    audioPlayer.play(); 
                } else { 
                    currentSongIndex = (currentSongIndex + 1) % songs.length; 
                    loadSong(currentSongIndex); 
                } 
            } 
        }) ; 

        function initializePlayer() { 
            if (songs.length > 0) { 
                loadSong(currentSongIndex); 
            } 
        } 

        fetch('https://raw.githubusercontent.com/Thaolga/Rules/main/Clash/songs.txt') 
            .then (response => response.text()) 
            .then(data => { 
                songs = data.split('\n').filter(url => url.trim() !== ''); 
                initializePlayer(); 
                console.log(songs); 
            }) 
            .catch(error => console.error ('Error fetching songs:', error)); 

        window.onload = function() { 
            audioPlayer.pause(); 
            setTimeout(() => { 
                document.getElementById('mobile-controls').classList.add('hidden '); 
            }, 30000); 
        }; 
    </script> 
</body> 
</html> 


<?php 
date_default_timezone_set('Asia/Shanghai'); 
?> 

<!DOCTYPE html> 
<html lang="zh-CN" > 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Voice broadcast system</title> 
</head > 
<body> 
   <script> 
        const city = 'Beijing'; // Replace with your city name 
        const apiKey = 'fc8bd2637768c286c6f1ed5f1915eb22'; 

        function speakMessage(message) { 
            const utterance = new SpeechSynthesisUtterance(message); 
            utterance.lang = 'zh-CN'; 
            speechSynthesis.speak(utterance); 
        } 

        function getGreeting() { 
            const hours = new Date().getHours(); 
            if (hours >= 5 && hours < 12) return 'Good morning! ';
            if (hours >= 12 && hours < 18) return 'Good afternoon! '; 
            if (hours >= 18 && hours < 22) return 'Good evening! '; 
            return 'It's late, get some rest! '; 
        } 

        function speakCurrentTime() { 
            const now = new Date(); 
            const hours = now.getHours(); 
            const minutes = now.getMinutes().toString().padStart(2, '0'); 
            const seconds = now.getSeconds().toString().padStart(2, '0'); 
            const currentTime = `${hours}points${minutes}minutes${seconds}seconds`; 

            const timeOfDay = (hours >= 5 && hours < 8) ? 'Early morning' 
                              : (hours >= 8 && hours < 11) ? 'Morning' 
                              : (hours >= 11 && hours < 13) ? 'Noon' 
                              : (hours >= 13 && hours < 18) ? 'Afternoon' 
                              : (hours >= 18 && hours < 20) ? 'Evening' 
                              : (hours >= 20 && hours < 24) ? 'Evening' 
                              : 'Morning'; 

            speakMessage(`${getGreeting()} 现在北京时间: ${timeOfDay}${currentTime}`); 
        } 

        function updateTime() { 
            const now = new Date(); 
            const hours = now.getHours(); 
            const timeOfDay = (hours >= 5 && hours < 8) ? 'Early morning' 
                              : (hours >= 8 && hours < 11) ? 'Morning' 
                              : (hours >= 11 && hours < 13) ? 'Noon' 
                              : (hours >= 13 && hours < 18) ? 'Afternoon' 
                              : (hours >= 18 && hours < 20) ? 'Evening' 
                              : (hours >= 20 && hours < 24) ? 'Evening' 
                              : 'Morning'; 

            if (now.getMinutes() === 0 && now.getSeconds() === 0) { 
                speakMessage(`Hourly report, now is Beijing time ${timeOfDay} ${hours} o'clock`); 
            } 
        } 

        const websites = [ 
            'https://www.youtube.com/', 
            'https://www.google.com/', 
            'https://www.facebook.com/', 
            'https://www.twitter.com/', 
            'https://www.github.com/' 
        ]; 

        function getWebsiteStatusMessage(url, status) { 
            const statusMessages = { 
                'https://www.youtube.com/': status ? 'YouTube website is accessible. ' : 'Unable to access YouTube website, please check network connection. ', 
                'https://www.google.com/': status ? 'Google website is accessible. ' : 'Unable to access Google website, please check network connection. ', 
                'https://www.facebook.com/': status ? 'Facebook website is accessible. ' : 'Unable to access Facebook website, please check network connection. ',
                'https://www.twitter.com/': status ? 'Twitter website is accessible. ' : 'Unable to access Twitter website, please check network connection. ', 
                'https://www.github.com/': status ? 'GitHub website is accessible. ' : 'Unable to access GitHub website, please check network connection. ', 
            }; 

            return statusMessages[url] || (status ? `${url} website is accessible normally.` : `Unable to access ${url} website, please check the network connection.`); 
        } 

        function checkWebsiteAccess(urls) { 
            const statusMessages = []; 
            let requestsCompleted = 0; 

            urls.forEach(url => { 
                fetch(url, { mode: 'no-cors' }) 
                    .then(response => { 
                        const isAccessible = response.type === 'opaque'; 
                        statusMessages.push(getWebsiteStatusMessage(url, isAccessible)); 
                        
                        if (!isAccessible && url === 'https://www.youtube.com/') { 
                            speakMessage('Unable to access YouTube website, please check the network connection.'); 
                        } 
                    }) 
                    .catch(() => { 
                        statusMessages.push(getWebsiteStatusMessage(url, false)); 
                        
                        if (url === 'https://www.youtube.com/') { 
                            speakMessage('Unable to access YouTube website, please check network connection.'); 
                        } 
                    }) 
                    .finally(() => { 
                        requestsCompleted++; 
                        if (requestsCompleted === urls.length) { 
                            speakMessage(statusMessages.join(' ')); 
                        } 
                    }); 
            }); 
        } 

        function getRandomPoem() { 
            const poems = [ 
                'Red beans grow in the south, and spring brings a few branches. ', 'Being a stranger in a foreign land, I miss my family even more during festivals. ', 
                'The bright moon rises over the sea, and we share this moment across the world. ', 'I wish you a long life, and we can share the beauty of the moon even though we are thousands of miles apart. ', 
                'The south of the Yangtze River is beautiful, and the scenery is familiar to me. ', 'Don't you see that the water of the Yellow River comes from the sky, rushes to the sea and never returns. ', ' 
                The dew is white tonight, and the moon is bright in my hometown. ', 'Since ancient times, autumn has been sad and lonely, but I say that autumn is better than spring. ', 
                'The monkeys on both sides of the river are crying, and the boat has already passed through thousands of mountains. ', 'After going two or three miles, there are four or five smoke-filled villages. ', 
                'Why do you say goodbye? My heart is following the blue clouds. ', 'The wind is strong and the sky is high, the monkeys are howling sadly, the sand is clear and the white birds are flying back. ', 
                'Although the brocade city is said to be fun, it is better to return home early. ', 'Looking at the red building in the cold winter at Baixia Posthouse, the red building is cold in the rain. ', 
                'Mooring at Niuzhu at night, reminiscing about the past, Niuzhu West River at night. ', 'After the rain in the empty mountains, the weather turns to autumn late. ', 
                'Say goodbye in the mountains, close the wooden door at dusk. ', 'The cicadas are sad, facing the long pavilion late, the sudden rain has just stopped. ', 
                'The lake is clear at first and then rainy, and the water surface is blurred with clear light. ', 'An old man in a straw hat and raincoat is fishing alone in the cold river snow. ', 
                'The Yellow River is far away among the white clouds, and there is a lonely city and thousands of mountains. ', 'I asked the boy under the pine tree, and he said that the master had gone to collect herbs. ',
                'There is a family in the depths of the white clouds, and a jade flute is played in the Yellow Crane Tower. ', 'Withered vines, old trees, crows, small bridges, flowing water, and people's homes. ', 
                'The cold mountain turns green, and the autumn water and the sky are the same color. ', 'The flowers are the same year after year, but the people are different year after year. ', 
                'The spring scenery of Jinjiang River comes from heaven and earth, and the floating clouds of Yulei change from ancient times to the present. ', 'The rain on Tianjie is as soft as butter, and the grass looks far away but not near. ', 
                'The Yangtze River surrounds the city, and the fish are delicious. The Su Causeway is a good place in the spring morning. ' 
            ];
            return poems[Math.floor(Math.random() * poems.length)]; 
        } 

        function speakRandomPoem() { 
            const poem = getRandomPoem(); 
            speakMessage(`${poem}`); 
        } 

        function speakWeather(weather) { 
            const descriptions = { 
                "clear sky": "Sunny", "few clouds": "A few clouds", "scattered clouds": "Cloudy", 
                "broken clouds": "Cloudy", "shower rain": "Showers", "rain": "Rain", 
                "light rain": "Light rain", "moderate rain": "Moderate rain", "heavy rain": "Heavy rain", 
                "very heavy rain": "Extreme rain": "Extreme rain", 
                "thunderstorm": "Thunderstorm", "thunderstorm with light rain": "Thunderstorm", "thunderstorm with heavy rain": "Strong thunderstorm", 
                "snow": "Snow", "light snow": "Light snow", "moderate snow": "Moderate snow", "heavy snow": "Heavy snow", 
                "very heavy snow": 
                }; const weatherDescription = descriptions[weather.weather[0].description.toLowerCase()] || weather.weather[0 ] 
                .description 
                ; 
                const 
            temperature 
            = 
                weather.main.temp 
            ; 
            const 
                tempMax = weather.main.temp_max; 
            const tempMin = weather.main.temp_min; 
            const humidity = weather.main.humidity; 
            const windSpeed ​​= weather.wind.speed; 
            const visibility = weather.visibility / 1000; 
            let message = `Here is today's weather forecast for ${city}: Current temperature is ${temperature} degrees Celsius, ${weatherDescription}. ` + 
                          `Today's high is expected to be ${tempMax} degrees Celsius, and tonight's low is expected to be ${tempMin} degrees Celsius. `; 
            if (weather.rain && weather.rain['1h']) { 
                var rainProbability = weather.rain['1h']; 
                message += ` There is a ${rainProbability * 100}% chance of rain in the next hour. `; 
            } else if (weather.rain && weather.rain['3h']) { 
                var rainProbability = weather.rain['3h']; 
                message += ` There is a ${rainProbability * 100}% chance of rain in the next three hours. `; 
            } else { 
                message += ' Low probability of rain today. '; 
            } 
            message += ` Southwest wind at ${windSpeed} meters per hour. ` +




                       ` The humidity is ${humidity}%. `; 

            if (weatherDescription.includes('Sunny') || weatherDescription.includes('Sunny')) { 
                message += ` The UV index is moderate. Please remember to apply sunscreen if you go out. `; 
            } else if (weatherDescription.includes('Rain') || weatherDescription.includes('Showers') || weatherDescription.includes('Thunderstorm')) { 
                message += ` It is recommended that you bring an umbrella when you go out. `; 
            } 

            message += ` The visibility is ${visibility} kilometers. ` + 
                       `Please stay safe, stay in a good mood, and have a nice day! `; 

            speakMessage(message); 
        } 

        function fetchWeather() { 
            const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric&lang=zh_cn`; 
            fetch(apiUrl) 
                .then(response => response.ok ? response.json() : Promise.reject('Network response is abnormal.')) 
                .then(data => { 
                    if (data.weather && data.main) { 
                        speakWeather(data); 
                    } else { 
                        console.error('Unable to obtain weather data.'); 
                    } 
                }) 
                .catch(error => console.error('Error in obtaining weather data:', error)); 
        } 

        window.onload = function() { 
            speakMessage('Welcome to the voice broadcast system!'); 
            checkWebsiteAccess(websites); 
            speakCurrentTime(); 
            fetchWeather(); 
            speakRandomPoem(); 
            setInterval(updateTime, 1000); 
            speakMessage('Your music playback has been paused. Press the ESC key to resume.'); 
        }; 
    </script> 
</body> 
</html>
