from flask import Flask, request, render_template, jsonify, after_this_request, send_from_directory
from flask_cors import CORS
from classes import Source, Result
import os
import gemini_incl as gem
import result_prep as prep
import re
import unicodedata

app = Flask(__name__)
CORS(app)
current_directory = os.path.dirname(os.path.abspath(__file__))
app.config['UPLOAD_FOLDER'] = f'{current_directory}/uploads'

def work_pdf(ai,out_ai,source_tab,files,mod,edu):
    out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod),gem.translate(edu)))
    print(f"out_ai : {out_ai}")
    cleaned_ai = prep.clean_split_str(out_ai[0])
    print(f"cleaned_ai : {cleaned_ai}")
    results_with_pdfs = []
    for i in range(0,len(cleaned_ai)):
        prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
        relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
        results_with_pdfs.append({
            'pdf_path': relative_pdf_path,
            'filename': files[i].filename
        })
    return results_with_pdfs


# Add route to serve CSS file from templates directory
@app.route('/style.css')
def serve_css():
    return send_from_directory('templates', 'style.css')

def remove_accents(input_str):
    # Transforme les caractères accentués en non accentués
    nfkd_form = unicodedata.normalize('NFKD', input_str)
    return ''.join([c for c in nfkd_form if not unicodedata.combining(c)])

def create_last_dict(files,tab):
    last_dict={}
    for idx, file in enumerate(files, start=1):
        last_dict.update({file.filename: tab[idx-1]})

def prepare_source_tab(mid_res_dict):
    source_tab = []
    for i in mid_res_dict.values():
        source_tab.append(i)
    for i in range(1, len(source_tab)):
        source_tab[i] = "---SOURCE_START---" + source_tab[i] + "---SOURCE_END---"
    return source_tab


def handle_option_2(ai, source_tab, mod, files):
    out_ai = [ai.invoke_quiz(source_tab, gem.translate(mod))]
    cleaned_ai = [prep.clean_json(out_ai[0])]
    # json_paths = []
    # for i in range(0, len(cleaned_ai) - 1):
    #     json_paths.append(prep.to_json(cleaned_ai[i], f"quiz_{files[i].filename.replace('pdf','json')}"))
    return out_ai, cleaned_ai, []


def handle_option_3(ai, source_tab, mod, edu, files):
    out_ai = [ai.invoke_abs(source_tab, gem.translate(mod), gem.translate(edu))]
    out_ai += [ai.invoke_quiz(source_tab, gem.translate(mod))]
    cleaned_ai = [prep.clean_split_str(out_ai[0])]
    results_with_pdfs = []
    for i in range(0, len(cleaned_ai) - 1):
        prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
        relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
        results_with_pdfs.append({
            'pdf_path': relative_pdf_path,
            'filename': files[i].filename
        })
    return out_ai, cleaned_ai, results_with_pdfs


def handle_option_4(ai, source_tab, mod, edu, files):
    out_ai = [ai.invoke_abs(source_tab, gem.translate(mod), gem.translate(edu))]
    cleaned_ai = prep.clean_split_str(out_ai[0])
    results_with_pdfs = []
    for i in range(0, len(cleaned_ai)):
        prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
        relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
        results_with_pdfs.append({
            'pdf_path': relative_pdf_path,
            'filename': files[i].filename
        })
    return out_ai, cleaned_ai, results_with_pdfs


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

@app.route('/download/<path:filename>')
def download_file(filename):
    return send_from_directory('download', filename, as_attachment=True)

@app.route('/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        selected_option = request.form.get('option')
        mod = request.form.get('modifier')
        edu = request.files.get('mode')
        files = request.files.getlist('files')
        if not files or (len(files) == 1 and files[0].filename == ''):
            files = request.files.getlist('files[]')

        print(f'files : {files}')
        source = Source(files, selected_option, mod, upload_folder=app.config['UPLOAD_FOLDER'])
        mid_res = Result(source)
        mid_res_dict = mid_res.to_dict()
        ai = gem.AI()
        source_tab = prepare_source_tab(mid_res_dict)
        out_ai, cleaned_ai, results_with_pdfs = [], [], []

        match selected_option:
            case "2":
                out_ai, cleaned_ai, results_with_pdfs = handle_option_2(ai, source_tab, mod, files)
            case "3":
                out_ai, cleaned_ai, results_with_pdfs = handle_option_3(ai, source_tab, mod, edu, files)
            case "4":
                out_ai, cleaned_ai, results_with_pdfs = handle_option_4(ai, source_tab, mod, edu, files)
            case _:
                results_with_pdfs = work_pdf(ai, out_ai, source_tab, files, mod, edu)
        print(f"cleaned_ai : {cleaned_ai}")
        print(f"out_ai : {out_ai}")
        return out_ai
    return render_template('index.html')


if __name__ == "__main__":
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)