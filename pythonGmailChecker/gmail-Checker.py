#!/usr/bin/env python
"""Sending Email
Created by: Michael Matthews
Date: Oct. 7th 2015
"""

wait = 50

#importing necessary libraries
import smtplib
import imaplib
import email
import time

#setting start time
start = time.time()

#Sending the message
fromaddr = 'exchangetestbyu2@gmail.com'
toaddr = 'michaeljmatthews@byu.edu'
msg = 'Subject: %s\n\n%s' % (start, 'send this')

#Credentials
username = 'exchangetestbyu2@gmail.com'
password = 'pQqCEJpUVFmArBaWLAcPno6aGUgvermAhb4UYNDFkdWJxowryn'

#The actual mail send
server = smtplib.SMTP('smtp.gmail.com:587')
server.starttls()
server.login(username, password)
server.sendmail(fromaddr, toaddr, msg)
server.quit()

#Having the script wait so that the email can get there
time.sleep(wait) 

#Opening up GMAIL account
def extract_body(payload):
    if isinstance(payload,str):
        return payload
    else:
        return '\n'.join([extract_body(part.get_payload()) for part in payload])

conn = imaplib.IMAP4_SSL("imap.gmail.com", 993)
conn.login("exchangetestbyu@gmail.com", "pQqCEJpUVFmArBaWLAcPno6aGUgvermAhb4UYNDFkdWJxowryn")
conn.select()
typ, data = conn.search(None, 'UNSEEN')
try:
    for num in data[0].split():
        typ, msg_data = conn.fetch(num, '(RFC822)')
        for response_part in msg_data:
            if isinstance(response_part, tuple):
                msg = email.message_from_string(response_part[1])
                sentFrom = msg['from']
                subject = msg['subject']
               
               	""""
                payload=msg.get_payload()
                body=extract_body(payload)
                print(body)"""
              
        typ, response = conn.store(num, '+FLAGS', r'(\Seen)')
finally:
    try:
        conn.close()
    except:
        pass
    conn.logout()

#This subtracts the send date and return date
###Converting string to float

while True:
    try:
        parseSubject = subject[4:]
        subjectFloat = int(float(parseSubject))
        done = time.time()
        elapsed = done - subjectFloat
#The line below calculates when the script started and how much later the script was verified

        if elapsed > 60:
            minutes = elapsed / 60 
            print ("The was sent and then verified this many minutes later: ")
            print minutes 
            break
    except NameError:
        print "Unable to verify email.  Please run a check"
        break

   
  




