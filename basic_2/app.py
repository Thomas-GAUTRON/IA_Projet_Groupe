from flask import Flask, request, render_template, jsonify
from classes import Source, Result
import os
import gemini_incl as gem
import result_prep as prep

app = Flask(__name__)
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

# Add route to serve PDF files from download directory
@app.route('/download/<path:filename>')
def download_file(filename):
    from flask import send_from_directory
    return send_from_directory('download', filename)

# Add route to serve CSS file from templates directory
@app.route('/style.css')
def serve_css():
    from flask import send_from_directory
    return send_from_directory('templates', 'style.css')

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

@app.route('/generate_quiz_pdf', methods=['POST'])
def generate_quiz_pdf():
    data = request.get_json()
    latex = data.get('latex')
    filename = data.get('filename', 'quiz.pdf')
    if not latex:
        return jsonify({'error': 'Aucun contenu LaTeX fourni.'}), 400
    # On s'assure que le nom de fichier finit par .pdf
    if not filename.endswith('.pdf'):
        filename += '.pdf'
    try:
        prep.to_pdf(latex, filename)
        return jsonify({'pdf_path': f'/download/{filename}'})
    except Exception as e:
        return jsonify({'error': str(e)}), 500

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