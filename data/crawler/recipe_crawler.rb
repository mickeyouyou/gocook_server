# encoding: utf-8
# 下厨房 0404
require 'rubygems'
require 'mechanize'
require 'nokogiri'
require 'uri'
require 'json'

class GoCook

  def initialize
    @agent = Mechanize.new
    @file_agent = Mechanize.new
    @file_agent.pluggable_parser.default = Mechanize::Download
    @error_log = ''

  end

  def get_some_recipe
    day = Time.now
    filename = 'recipe_' + day.year.to_s + '_' + day.month.to_s + '_' +day.day.to_s + '.txt'
    if File.exist?(filename)
      File.delete(filename)
    end
    if File.exist?(filename)
      file = File.new(filename, 'a')
    else
      file = File.open(filename,'w')
    end

    puts 'start...'

    20.times do |i|
      crawl_url = "http://www.xiachufang.com/category/40076/?page=#{i+1}"

      page = @agent.get(crawl_url)

      content = NKF.nkf('-w', page.content)

      doc = Nokogiri::HTML(content)

      doc.search('//div[@class="user-recipe-list"]/ul').each do |ul|
        ul.css('/li').each do |li|

          recipe_url ||= ''
          recipe_small_img ||= ''
          recipe_name ||= ''
          recipe_intro ||= ''
          dish_count ||= ''
          collect_count ||= 0
          brow_count ||= 0
          comment_cont ||= 0

          recipe_catgory = ''

          recipe_material ||= ''
          recipe_tips ||= ''
          recipe_steps ||= ''

          author ||= ''
          rating ||= ''


          if li.css('/a').count > 0
            recipe_url = 'http://www.xiachufang.com' + li.css('/a').first[:href]
          end

          if li.css('/a/img').count > 0
            image_url = li.css('/a/img').first[:src]
            recipe_small_img = image_url.split('/').last
            image_path = './recipe/140/' + recipe_small_img
            if File.exist?(image_path)
              File.delete(image_path)
            end
            @file_agent.get(image_url).save(image_path)
          end

          if li.css('/p/a').count > 0
            recipe_name = li.css('/p/a').first.content
            #puts recipe_name
          end

          if li.css('/p/span').count > 0
            tmp_string = li.css('/p/span').first.text
            tmp_array = []
            tmp_string.split("\n").each do |c|
              if c.strip != ''
                tmp_array.push c.strip
              end
            end

            if tmp_array.count == 3

              tmp_array[1] =~ /(综合评分\s\s(.+?)\s（)*(.+?)做过(）)*/
              rating = $2
              dish_count = $3

              author = tmp_array[2]

              #puts rating
              #puts dish_count
              #puts author
              #
              #puts "\n\n"
            end
          end

          detail_page = @agent.get(recipe_url)

          detail_content = NKF.nkf('-w', detail_page.content)

          doc = Nokogiri::HTML(detail_content.gsub("\n",''))

          doc.css('//div[@class="g-recipe-img-big"]').each do |bigimg_div|

            bigimg_div[:style] =~ /background-image:url\((.+?)\);/
            image_url = $1
            recipe_big_img = image_url.split('/').last
            image_path = './recipe/526/' + recipe_big_img
            if File.exist?(image_path)
              File.delete(image_path)
            end
            @file_agent.get(image_url).save(image_path)
          end

          count_array = doc.css('//div[@class="g-recipe-count"]//div').first.text.gsub("\n",'').gsub(' ','').split('|')
          dish_count = count_array[0].chomp.strip.gsub(' ','').to_i
          collect_count = count_array[1].chomp.strip.gsub(' ','').to_i
          brow_count = count_array[2].chomp.strip.gsub(' ','').to_i

          intro_element = doc.css('//div[@class="g-recipe-intro"]').first
          if intro_element
            recipe_intro = intro_element.content
          end

          count = 0
          doc.css('//table[@class="g-recipe-ing"]//tr').each do |tr|
            tr.css('/td').each do |td|
              td.content.split(' ').each do |split_it|
                if count > 0
                  recipe_material += '|'
                end
                count += 1
                recipe_material += split_it
              end
              split_count = td.content.split(' ').count
              if split_count == 1
                recipe_material += '|'
              end
            end
          end

          recipe_steps += '{"steps":['
          doc.search('//div[@class="g-recipe-steps"]/ol/li').each_with_index do |step_div, index|
            step_img_url = ''
            step_content = ''
            step_num = step_div.css('/em').first.content.chop
            if step_div.css('/span').count > 0
              sleep(0.2)

              step_img_url = step_div.css('/img').first[:src]
              recipe_step_img = step_img_url.split('/').last
              image_path = './recipe/step/' + recipe_step_img
              if File.exist?(image_path)
                File.delete(image_path)
              end
              @file_agent.get(step_img_url).save(image_path)


              step_content = step_div.css('/span').first.content
            else
              #puts step_div.to_s
              step_div.to_s =~ /<\/em>(.+?)<br/m
              step_content = $1.strip
            end
            #puts step_num
            #puts step_content
            #puts step_img
            step_tmp = ''
            if index > 0
              step_tmp  = ','
            end
            step_tmp += '{"no":' + step_num + ',"content":"' + step_content + '","img":"' + step_img_url + '"}'
            recipe_steps += step_tmp
          end
          recipe_steps += ']}'

          recipe_tips = doc.css('//div[@class="g-recipe-tips g-f14"]').first
          if recipe_tips
            recipe_tips = recipe_tips.content
          else
            recipe_tips = ''
          end


          puts recipe_name
          puts recipe_intro
          puts dish_count
          puts collect_count
          puts brow_count
          puts comment_cont

          puts recipe_catgory

          puts recipe_material

          puts recipe_steps

          puts recipe_tips

          file = File.new(filename, 'a')
          file.puts 'insert into recipe (user_id, create_time, name, recipe.desc, collected_count, dish_count, ' +
                        'comment_count, browse_count, catgory, cover_img, materials, recipe_steps, tips) ' +
                        "values (1, CURRENT_TIMESTAMP, \"#{recipe_name.gsub('"','\"')}\", \"#{recipe_intro.gsub('"','\"').gsub("\n",'').gsub("\r","\\n").strip}\", " +
                        "#{collect_count}, #{dish_count}, #{comment_cont}, #{brow_count}, \"#{recipe_catgory.gsub('"','\"')}\", \"#{recipe_small_img}\", " +
                        "\"#{recipe_material.gsub('"','\"')}\", \"#{recipe_steps.gsub('"','\"')}\",\"#{recipe_tips.gsub('"','\"').gsub("\n",'').gsub("\r","\\n")}\");\n"

          file.close
        end
      end
    end



  end
end



cook = GoCook.new
cook.get_some_recipe