from flask import Flask, request, render_template
from classes import Source, Result
import os
import gemini_incl as gem
import result_prep as prep

app = Flask(__name__)
app.config['UPLOAD_FOLDER'] = 'uploads'

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

@app.route('/', methods=['GET', 'POST'])
def upload_file():
    if request.method == 'POST':
        selected_option = request.form.get('option')
        mod = request.form.get('modifier')
        files = request.files.getlist('files')
        edu = request.files.get('mode')

        source = Source(files, selected_option, mod, upload_folder=app.config['UPLOAD_FOLDER'])
        mid_res = Result(source)
        mid_res_dict = mid_res.to_dict()
        ai = gem.AI()
        source_tab =[]
        out_ai=[]
        cleaned_ai=[]
        
        for i in mid_res_dict.values():
            source_tab.append(i)
        for i in range(1,len(source_tab)):
            source_tab[i]="---SOURCE_START---"+source_tab[i]+"---SOURCE_END---"
        
        match selected_option:
            case "2":
                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                cleaned_ai = prep.clean_json(out_ai[0])
                for i in range(0,len(cleaned_ai)):
                    prep.to_json(cleaned_ai[i],f"quiz_{files[i].filename.replace('pdf','json')}")
                    # Add for lauching quizes

                if not(gem.translate(mod)):
                    prep.to_json(out_ai[0],f"quiz_{files[0].filename.replace('pdf','json')}")
                else:
                    for i in range(0,len(out_ai)):
                        cleaned_ai.append(prep.clean_json(out_ai[i]))
                        prep.to_json(cleaned_ai[i],f"quiz_{files[i].filename.replace('pdf','json')}")
            case "3":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_split_str(out_ai[0])

                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])

                out_ai.append(ai.invoke_quiz(source_tab,gem.translate(mod)))
                temp,len_cleaned_ai = prep.clean_json(out_ai[1])

                for i in range(0,len_cleaned_ai):
                    cleaned_ai.append(temp[i])
                #print(f"Taille cleaned {len(cleaned_ai)}")
                results_with_pdfs = []
                for i in range(0,len(cleaned_ai)-len_cleaned_ai):
                    print(f"i : {i}")
                    #print(f"fichier : {files[i].filename}")
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
                    # Create relative path for web access
                    relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })
                for i in range(0,len_cleaned_ai):
                    prep.to_json(cleaned_ai[i+len_cleaned_ai],f"quiz_{files[i].filename.replace('pdf','json')}")
                    
            case "4":
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod)))
                cleaned_ai = prep.clean_split_str(out_ai[0])
                out_ai.append(ai.invoke_quiz(cleaned_ai,gem.translate(mod)))
                cleaned_ai.append(prep.clean_json(out_ai[1]))
                results_with_pdfs = []
                for i in range(0,len(cleaned_ai)-1):
                    #print(f"fichier : {files[i].filename}")
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
                    # Create relative path for web access
                    relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })
                
            case _:
                out_ai.append(ai.invoke_abs(source_tab,gem.translate(mod),gem.translate(edu)))
                print(f"out_ai : {out_ai}")
                cleaned_ai = prep.clean_split_str(out_ai[0])
                print(f"cleaned_ai : {cleaned_ai}")
                
                # Collect PDF paths and create results with only filename and PDF path
                results_with_pdfs = []
                for i in range(0,len(cleaned_ai)):
                    #print(f"fichier : {files[i].filename}")
                    prep.to_pdf(cleaned_ai[i], f"abstract_{files[i].filename}")
                    # Create relative path for web access
                    relative_pdf_path = os.path.join('download', f"abstract_{files[i].filename}")
                    results_with_pdfs.append({
                        'pdf_path': relative_pdf_path,
                        'filename': files[i].filename
                    })
                

        return render_template('result.html', results=results_with_pdfs)#result = final array for abstract

    return render_template('index.html')
if __name__ == "__main__":
    os.makedirs(app.config['UPLOAD_FOLDER'], exist_ok=True)
    app.run(debug=True)