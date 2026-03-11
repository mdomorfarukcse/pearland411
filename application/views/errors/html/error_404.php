<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>404 - Page Not Found</title>
	<style>
		* {
			margin: 0;
			padding: 0;
			box-sizing: border-box;
		}

		body {
			font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			background: linear-gradient(135deg, #180f5e 0%, #2a1a8f 100%);
			min-height: 100vh;
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			overflow-x: hidden;
		}

		.container {
			text-align: center;
			padding: 2rem;
			max-width: 800px;
			position: relative;
			z-index: 10;
		}

		.error-code {
			font-size: clamp(4rem, 15vw, 8rem);
			font-weight: 800;
			color: #8bc541;
			line-height: 1;
			margin-bottom: 1rem;
			text-shadow: 0 0 30px rgba(139, 197, 65, 0.5);
		}

		h1 {
			font-size: clamp(1.5rem, 5vw, 2.5rem);
			margin-bottom: 1rem;
			font-weight: 600;
		}

		p {
			font-size: clamp(1rem, 3vw, 1.2rem);
			margin-bottom: 2.5rem;
			opacity: 0.9;
			line-height: 1.6;
		}

		.btn-home {
			display: inline-block;
			padding: 1rem 2.5rem;
			background: #8bc541;
			color: #180f5e;
			text-decoration: none;
			border-radius: 50px;
			font-weight: 600;
			font-size: 1.1rem;
			transition: all 0.3s ease;
			box-shadow: 0 10px 30px rgba(139, 197, 65, 0.3);
		}

		.btn-home:hover {
			transform: translateY(-3px);
			box-shadow: 0 15px 40px rgba(139, 197, 65, 0.5);
			background: #9dd350;
		}

		.svg-container {
			margin-bottom: 2rem;
			position: relative;
		}

		/* Background decoration */
		.bg-circles {
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			z-index: 1;
			pointer-events: none;
		}

		.circle {
			position: absolute;
			border-radius: 50%;
			background: rgba(139, 197, 65, 0.1);
			animation: float 20s infinite ease-in-out;
		}

		.circle:nth-child(1) {
			width: 300px;
			height: 300px;
			top: -150px;
			left: -150px;
			animation-delay: 0s;
		}

		.circle:nth-child(2) {
			width: 200px;
			height: 200px;
			bottom: -100px;
			right: -100px;
			animation-delay: 5s;
		}

		.circle:nth-child(3) {
			width: 150px;
			height: 150px;
			top: 50%;
			left: 10%;
			animation-delay: 10s;
		}

		@keyframes float {

			0%,
			100% {
				transform: translate(0, 0) scale(1);
			}

			33% {
				transform: translate(30px, -30px) scale(1.1);
			}

			66% {
				transform: translate(-20px, 20px) scale(0.9);
			}
		}

		/* SVG Animations */
		.astronaut {
			animation: astronautFloat 6s ease-in-out infinite;
		}

		@keyframes astronautFloat {

			0%,
			100% {
				transform: translate(0, 0) rotate(0deg);
			}

			50% {
				transform: translate(10px, -20px) rotate(5deg);
			}
		}

		.planet {
			animation: planetRotate 20s linear infinite;
			transform-origin: center;
		}

		@keyframes planetRotate {
			from {
				transform: rotate(0deg);
			}

			to {
				transform: rotate(360deg);
			}
		}

		.star {
			animation: starTwinkle 3s ease-in-out infinite;
		}

		@keyframes starTwinkle {

			0%,
			100% {
				opacity: 0.3;
			}

			50% {
				opacity: 1;
			}
		}

		.satellite {
			animation: satelliteOrbit 8s linear infinite;
			transform-origin: 200px 150px;
		}

		@keyframes satelliteOrbit {
			from {
				transform: rotate(0deg) translateX(100px) rotate(0deg);
			}

			to {
				transform: rotate(360deg) translateX(100px) rotate(-360deg);
			}
		}
	</style>
</head>

<body>
	<div class="bg-circles">
		<div class="circle"></div>
		<div class="circle"></div>
		<div class="circle"></div>
	</div>

	<div class="container">
		<div class="svg-container">
			<svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg" style="max-width: 500px; width: 100%; height: auto;">
				<!-- Stars -->
				<circle class="star" cx="50" cy="50" r="2" fill="#8bc541" style="animation-delay: 0s;" />
				<circle class="star" cx="350" cy="80" r="1.5" fill="#8bc541" style="animation-delay: 1s;" />
				<circle class="star" cx="100" cy="30" r="1" fill="#8bc541" style="animation-delay: 2s;" />
				<circle class="star" cx="320" cy="40" r="2" fill="#8bc541" style="animation-delay: 0.5s;" />
				<circle class="star" cx="150" cy="250" r="1.5" fill="#8bc541" style="animation-delay: 1.5s;" />
				<circle class="star" cx="300" cy="220" r="1" fill="#8bc541" style="animation-delay: 2.5s;" />

				<!-- Planet -->
				<g class="planet">
					<circle cx="200" cy="150" r="60" fill="#8bc541" opacity="0.3" />
					<circle cx="200" cy="150" r="50" fill="#8bc541" opacity="0.5" />
					<ellipse cx="200" cy="150" rx="50" ry="10" fill="none" stroke="#180f5e" stroke-width="2" opacity="0.3" />
					<ellipse cx="200" cy="150" rx="50" ry="20" fill="none" stroke="#180f5e" stroke-width="2" opacity="0.3" />
				</g>

				<!-- Astronaut -->
				<g class="astronaut">
					<!-- Body -->
					<ellipse cx="200" cy="180" rx="25" ry="30" fill="#ffffff" />

					<!-- Helmet -->
					<circle cx="200" cy="145" r="22" fill="#8bc541" opacity="0.3" />
					<circle cx="200" cy="145" r="18" fill="#ffffff" opacity="0.8" />

					<!-- Visor -->
					<ellipse cx="200" cy="145" rx="12" ry="10" fill="#180f5e" opacity="0.6" />

					<!-- Arms -->
					<rect x="175" y="175" width="6" height="20" fill="#ffffff" rx="3" transform="rotate(-30 178 185)" />
					<rect x="219" y="175" width="6" height="20" fill="#ffffff" rx="3" transform="rotate(30 222 185)" />

					<!-- Legs -->
					<rect x="190" y="205" width="6" height="18" fill="#ffffff" rx="3" />
					<rect x="204" y="205" width="6" height="18" fill="#ffffff" rx="3" />

					<!-- Backpack -->
					<rect x="205" y="175" width="15" height="20" fill="#8bc541" rx="2" />
					<circle cx="212" cy="180" r="2" fill="#180f5e" />
					<circle cx="212" cy="190" r="2" fill="#180f5e" />
				</g>

				<!-- Satellite -->
				<g class="satellite">
					<rect x="195" y="48" width="10" height="8" fill="#8bc541" />
					<rect x="188" y="52" width="6" height="1" fill="#8bc541" />
					<rect x="206" y="52" width="6" height="1" fill="#8bc541" />
					<line x1="188" y1="52" x2="182" y2="52" stroke="#8bc541" stroke-width="0.5" />
					<line x1="212" y1="52" x2="218" y2="52" stroke="#8bc541" stroke-width="0.5" />
				</g>
			</svg>
		</div>

		<div class="error-code">404</div>
		<h1>Page Not Found</h1>
		<p>Oops! Looks like you're lost in space. The page you're looking for has drifted into another dimension.</p>
		<a href="/" class="btn-home">Return to Home</a>
	</div>
</body>

</html>