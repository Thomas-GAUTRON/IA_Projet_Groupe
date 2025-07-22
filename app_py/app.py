from flask import Flask, request, render_template, jsonify
from flask_cors import CORS
from classes import Source, Result
import os
import gemini_incl as gem
import result_prep as prep
import re
import unicodedata
import uuid
import threading

'''
Main File for the complete application
'''
app = Flask(__name__)
CORS(app)
current_directory = os.path.dirname(os.path.abspath(__file__))
app.config['UPLOAD_FOLDER'] = f'{current_directory}/uploads'

tasks = {}  # Pour stocker l'état et les résultats des tâches

def process_files_task(task_id, files_data, selected_option, mod, edu):
    """
    Cette fonction s'exécute en arrière-plan pour traiter les fichiers.
    """
    try:
        # Recréer les objets 'FileStorage' à partir des données
        from werkzeug.datastructures import FileStorage
        files = []
        for file_info in files_data:
            file_storage = FileStorage(
                stream=open(file_info['path'], 'rb'),
                filename=file_info['filename'],
                content_type=file_info['content_type']
            )
            files.append(file_storage)

        # Le reste du code de traitement est identique à l'original
        source = Source(files, selected_option, mod, upload_folder=app.config['UPLOAD_FOLDER'])
        mid_res = Result(source)
        mid_res_dict = mid_res.to_dict()
        ai = gem.AI()
        source_tab =[]
        out_ai=[]
        cleaned_ai=[]
        quiz_file_name = []
        results_with_pdfs = []
        
        for i in mid_res_dict.values():
            source_tab.append(i)
        
        # Creation of the source_tab used by the AI
        for i in range(1,len(source_tab)):
            source_tab[i]="---SOURCE_START---"+source_tab[i]+"---SOURCE_END---"
        
        match selected_option:
            case "2":
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                cleaned_ai,temp = prep.clean_json(out_ai[0])
            case "3":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_split_str(out_ai[0])
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_json(out_ai[1])
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
            case "4":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_split_str(out_ai[0])
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
                out_ai.append(ai.invoke_quiz(cleaned_ai,gem.translate(mod)))
                temp, len_cleaned_ai = prep.clean_json(out_ai[1])
                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
            case _:
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod),gem.translate(edu)))
                cleaned_ai, temp = prep.clean_split_str(out_ai[0])

        tasks[task_id]['status'] = 'completed'
        tasks[task_id]['result'] = out_ai
    except Exception as e:
        tasks[task_id]['status'] = 'failed'
        tasks[task_id]['result'] = str(e)
    finally:
        # Nettoyer les fichiers temporaires
        for file_info in files_data:
            os.remove(file_info['path'])

# Add route to serve PDF files from download directory
@app.route('/download/<path:filename>')
def download_file(filename):
    """Servez le PDF; ?inline=1 pour aperçu dans le navigateur."""
    from flask import send_from_directory, request, Response
    inline = request.args.get('inline') == '1'
    resp: Response = send_from_directory(
        f'{current_directory}/download', filename,
        as_attachment=not inline,
        mimetype='application/pdf'
    )
    if inline:
        # Forcer Content-Disposition inline
        resp.headers["Content-Disposition"] = f"inline; filename={filename}"
    return resp

# Add route to serve CSS file from templates directory
@app.route('/style.css')
def serve_css():
    from flask import send_from_directory
    return send_from_directory('templates', 'style.css')

# Main route for the application
@app.route('/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        selected_option = request.form.get('option')
        mod = request.form.get('modifier')
        edu = request.form.get('mode') # Correction: 'mode' vient de form, pas de files
        files = request.files.getlist('files')
        if not files or (len(files) == 1 and files[0].filename == ''):
            files = request.files.getlist('files[]')

        if not files:
            return jsonify({'error': 'No files provided'}), 400

        # Sauvegarder les fichiers temporairement car on ne peut pas les passer directement à un thread
        files_data = []
        for f in files:
            temp_path = os.path.join(app.config['UPLOAD_FOLDER'], f.filename)
            f.save(temp_path)
            files_data.append({
                'path': temp_path,
                'filename': f.filename,
                'content_type': f.content_type
            })

        task_id = str(uuid.uuid4())
        tasks[task_id] = {'status': 'processing', 'result': None}
        
        thread = threading.Thread(target=process_files_task, args=(task_id, files_data, selected_option, mod, edu))
        thread.start()
        
        return jsonify({'task_id': task_id})

    return render_template('index.html')

@app.route('/result/<task_id>', methods=['GET'])
def get_result(task_id):
    task = tasks.get(task_id)
    if task is None:
        return jsonify({'status': 'not_found'}), 404
    
    if task['status'] == 'completed':
        # Optionnel: supprimer la tâche après l'avoir récupérée
        # result = task['result']
        # del tasks[task_id]
        # return jsonify({'status': 'completed', 'result': result})
        return jsonify({'status': 'completed', 'result': task['result']})
    elif task['status'] == 'failed':
        return jsonify({'status': 'failed', 'error': task['result']})
    else:
        return jsonify({'status': 'processing'})

def remove_accents(input_str):
    # Transforme les caractères accentués en non accentués
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return ''.join([c for c in nfkd_form if not unicodedata.combining(c)])

@app.route('/latex_to_pdf', methods=['POST'])
def latex_to_pdf():
    data = request.get_json()
    latex = data.get('latex')
    filename = data.get('filename', 'resume.pdf')
    title = data.get('title', 'Résumé généré')

    if not latex:
        return jsonify({'error': 'Aucun LaTeX fourni.'}), 400
    
    try:
        latex = prep.prepare_latex(latex, title, "IA PDF")
        print(latex)
        prep.to_pdf(latex, filename)
        pdf_url = f'http://127.0.0.1:5000/download/{filename}'
        return jsonify({'pdf_path': pdf_url})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/json_quiz_to_pdf', methods=['POST'])
def json_quiz_to_pdf():
    data = request.get_json()
    quiz_json = data.get('quiz_json')
    filename = data.get('filename', None)
    title = data.get('title', None)

    if not quiz_json:
        return jsonify({'error': 'Aucun quiz JSON fourni.'}), 400

    try:
        # Vérification de la structure
        if isinstance(quiz_json, str):
            quiz_json_obj = json.loads(quiz_json)
        else:
            quiz_json_obj = quiz_json

        if 'quiz' not in quiz_json_obj:
            return jsonify({'error': 'Le JSON ne contient pas de clé "quiz".'}), 400

        
        # Chemins absolu
        download_directory = os.path.join(current_directory, 'download')

       # Créer le répertoire de téléchargement s'il n'existe pas
        if not os.path.exists(download_directory):
            os.makedirs(download_directory)

        # Utiliser le titre comme nom de fichier si non fourni
        if not filename:
            raw_title = quiz_json_obj.get('courseTitle', 'quiz')
            # Enlève les accents
            raw_title = remove_accents(raw_title)
            # Nettoyer le titre pour en faire un nom de fichier valide
            filename = re.sub(r'[^a-zA-Z0-9_-]', '_', raw_title)
            filename = re.sub(r'_+', '_', filename)  # Remplacer plusieurs _ par un seul
            filename += '.pdf'

        if not filename.endswith('.pdf'):
            filename += '.pdf'

        print("Payload reçu :", quiz_json)
        latex = prep.json_quiz_to_latex(quiz_json, title=title)
        prep.to_pdf(latex, filename)

        # Renvoyer l'URL accessible du fichier PDF
        pdf_url = f'http://127.0.0.1:5000/download/{filename}'
        return jsonify({'pdf_path': pdf_url})

    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == "__main__":
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)