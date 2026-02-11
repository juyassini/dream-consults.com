import os
import csv
import smtplib
from email.message import EmailMessage
from flask import Flask, request, jsonify, send_from_directory
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__, static_folder='.', static_url_path='')

# Config from environment
SMTP_HOST = os.getenv('SMTP_HOST')
SMTP_PORT = int(os.getenv('SMTP_PORT') or 587)
SMTP_USER = os.getenv('SMTP_USER')
SMTP_PASS = os.getenv('SMTP_PASS')
CONTACT_RECIPIENT = os.getenv('CONTACT_RECIPIENT') or SMTP_USER or 'juyassini@gmail.com'

SUBMISSIONS_CSV = os.path.join(os.path.dirname(__file__), 'submissions.csv')

@app.route('/')
def index():
    return send_from_directory('.', 'index.html')

@app.route('/<path:path>')
def static_files(path):
    # serve other html, css, js and images from project root
    return send_from_directory('.', path)

@app.route('/api/contact', methods=['POST'])
def receive_contact():
    data = request.get_json() or request.form.to_dict()
    name = data.get('name')
    email = data.get('email')
    phone = data.get('phone', '')
    service = data.get('service', '')
    message = data.get('message', '')

    if not name or not email or not service or not message:
        return jsonify({'status': 'error', 'message': 'Missing required fields.'}), 400

    # Save to CSV (append)
    try:
        exists = os.path.exists(SUBMISSIONS_CSV)
        with open(SUBMISSIONS_CSV, 'a', newline='', encoding='utf-8') as f:
            writer = csv.writer(f)
            if not exists:
                writer.writerow(['name', 'email', 'phone', 'service', 'message'])
            writer.writerow([name, email, phone, service, message])
    except Exception as e:
        app.logger.error('Failed to save submission: %s', e)

    # Send email if SMTP configured
    if SMTP_HOST and SMTP_USER and SMTP_PASS and CONTACT_RECIPIENT:
        try:
            msg = EmailMessage()
            msg['Subject'] = f'Website Contact: {service} - {name}'
            msg['From'] = SMTP_USER
            msg['To'] = CONTACT_RECIPIENT
            msg.set_content(f'Name: {name}\nEmail: {email}\nPhone: {phone}\nService: {service}\n\nMessage:\n{message}')

            with smtplib.SMTP(SMTP_HOST, SMTP_PORT) as smtp:
                smtp.starttls()
                smtp.login(SMTP_USER, SMTP_PASS)
                smtp.send_message(msg)
        except Exception as e:
            app.logger.error('Failed to send email: %s', e)
            # do not fail the request if email fails

    return jsonify({'status': 'ok', 'message': 'Submission received.'})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=int(os.getenv('PORT', 5000)), debug=True)
