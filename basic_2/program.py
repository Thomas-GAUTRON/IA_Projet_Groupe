import os
from langchain_google_genai import ChatGoogleGenerativeAI
from pprint import pprint
import pdfkit

#if "GOOGLE_API_KEY" not in os.environ:
    #os.environ["GOOGLE_API_KEY"] = getpass.getpass("Enter your Google AI API key: ")
os.environ["GOOGLE_API_KEY"] = 'AIzaSyCRYchKjFliZhvG2EYQqJE1rnNYoD_T-fs'
gemini_flash_chat = ChatGoogleGenerativeAI(model="gemini-2.5-flash", temperature=0)
var = gemini_flash_chat.invoke("")
print(var.content)

def to_pdf(html_string, output_pdf_path, wkhtmltopdf_path=None):
    """
    Convert an HTML-marked-up string to a PDF file with correct formatting and paging.
    Args:
        html_string (str): The HTML content to convert.
        output_pdf_path (str): The path to save the PDF file.
        wkhtmltopdf_path (str, optional): Path to wkhtmltopdf executable. If None, assumes it's in PATH.
    """
    config = None
    if wkhtmltopdf_path:
        config = pdfkit.configuration(wkhtmltopdf=wkhtmltopdf_path)
    # Wrap in basic HTML structure if not already present
    if not html_string.strip().lower().startswith('<html'):
        html_full = f"""
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body {{ font-family: Arial, sans-serif; margin: 2cm; }}
                h1, h2, h3, h4, h5, h6 {{ page-break-after: avoid; }}
                p {{ orphans: 3; widows: 3; }}
                .page-break {{ page-break-before: always; }}
                u {{ text-decoration: underline; }}
                b, strong {{ font-weight: bold; }}
            </style>
        </head>
        <body>
            {html_string}
        </body>
        </html>
        """
    else:
        html_full = html_string
    pdfkit.from_string(html_full, output_pdf_path, configuration=config)

# Example usage:
if __name__ == "__main__":
    html = """
    <h1>Title</h1>
    <p>This is a <b>bold</b> and <u>underlined</u> text.<br>New page below.</p>
    <div class='page-break'></div>
    <h2>Page 2</h2>
    <p>More <strong>bold</strong> and <u>underlined</u> text.</p>
    """
    # Set your wkhtmltopdf path if needed
    wkhtmltopdf_path = r"C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe"
    to_pdf(html, "output.pdf", wkhtmltopdf_path=wkhtmltopdf_path)