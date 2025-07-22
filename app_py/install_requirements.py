import subprocess
import sys
'''
Small script to install dependencies for the project. 
Listed in requirements.txt
'''
requirements_file = 'requirements.txt'

with open(requirements_file, 'r') as f:
    packages = [line.strip() for line in f if line.strip() and not line.startswith('#')]

for package in packages:
    subprocess.check_call([sys.executable, '-m', 'pip', 'install', package]) 