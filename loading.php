<!DOCTYPE html>
<html>
<head>
    <title>Water Loader Animation</title>
    <meta name="viewport" content="width=device-width">
    <style>
        html {
            height: 100%;
        }

        body {
            background-image: radial-gradient(circle farthest-corner at center, #3C4B57 0%, #1C262B 100%);
            margin: 0;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .loader {
            position: relative;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            perspective: 800px;
            margin-bottom: 20px; /* Space between loader and text */
        }

        .inner {
            position: absolute;
            box-sizing: border-box;
            width: 100%;
            height: 100%;
            border-radius: 50%;
        }

        .inner.one {
            left: 0%;
            top: 0%;
            animation: rotate-one 1s linear infinite;
            border-bottom: 3px solid #EFEFFA;
        }

        .inner.two {
            right: 0%;
            top: 0%;
            animation: rotate-two 1s linear infinite;
            border-right: 3px solid #EFEFFA;
        }

        .inner.three {
            right: 0%;
            bottom: 0%;
            animation: rotate-three 1s linear infinite;
            border-top: 3px solid #EFEFFA;
        }

        @keyframes rotate-one {
            0% {
                transform: rotateX(35deg) rotateY(-45deg) rotateZ(0deg);
            }
            100% {
                transform: rotateX(35deg) rotateY(-45deg) rotateZ(360deg);
            }
        }

        @keyframes rotate-two {
            0% {
                transform: rotateX(50deg) rotateY(10deg) rotateZ(0deg);
            }
            100% {
                transform: rotateX(50deg) rotateY(10deg) rotateZ(360deg);
            }
        }

        @keyframes rotate-three {
            0% {
                transform: rotateX(35deg) rotateY(55deg) rotateZ(0deg);
            }
            100% {
                transform: rotateX(35deg) rotateY(55deg) rotateZ(360deg);
            }
        }

        .loading-text {
            font-size: 18px;
            font-weight: bold;
            color: #EFEFFA;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="loader">
        <div class="inner one"></div>
        <div class="inner two"></div>
        <div class="inner three"></div>
    </div>

    <div class="loading-text">Loading... Please wait</div>

    <script>
        // Extract the redirect URL from the query parameter
        const urlParams = new URLSearchParams(window.location.search);
        const redirectUrl = urlParams.get('redirect') || 'defaultPage.php'; // Default page if no redirect parameter is found

        // Set a timeout to redirect after 2 seconds
        setTimeout(() => {
            window.location.href = redirectUrl;
        }, 2000); // 2000 milliseconds = 2 seconds
    </script>
</body>
</html>
