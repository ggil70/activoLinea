#!usr/bin/env bash

# Input for sender email 
read -p "Enter your email : " sender

# Input for recipient email
read -p "Enter recipient email : " reciever

# Input for google app password
read -p "Enter your Google App password : " gapp

# Input for subject of the mail
read -p "Enter the subject of mail : " sub

# Using cat command to input multiline text to a variable (from file)

echo "Enter the body of mail : "
cat > tempfile.txt
body=$(cat tempfile.txt)
